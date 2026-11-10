var jenisSoal;
var fieldLinks;

var dataJodohkan;
var adaJawaban = false;

$(document).ready(function () {
    ajaxcsrf();
    $('.textsoal').summernote({
        placeholder: 'Tulis Soal disini',
        tabsize: 2,
        minHeight: 100,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'clear']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'file', 'math']],
            ['view', ['fullscreen', 'codeview']],
            ['cleaner', ['cleaner']],
        ],
        callbacks: {
            onChange: function (contents, $editable) {
                if ($('#jenis-id').val() == '7') {
                    clearTimeout(window.clozeParseTimeout);
                    window.clozeParseTimeout = setTimeout(function () {
                        updateClozeAnswers();
                    }, 500);
                }
            },
            onFileUpload: function (file) {
                var idtextarea = $(this);
                myOwnCallBack(file[0], idtextarea);
            },
            onImageUpload: function (images) {
                var idtextarea = $(this);
                myOwnCallBack(images[0], idtextarea);
            },
            onMediaDelete: function (target) {
                deleteImage(target[0].src);
            }
        },
        dialogsInBody: true,
    });

    $('.textjawaban').summernote({
        placeholder: 'Tulis Jawaban disini',
        tabsize: 2,
        minHeight: 72,
        maxHeight: null,
        disableResizeEditor: true,
        toolbar: [
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            //['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['view', ['fullscreen', 'codeview']],
            ['insert', ['picture', 'math']],
        ],
        callbacks: {
            onImageUpload: function (images) {
                var idtextarea = $(this);
                myOwnCallBack(images[0], idtextarea);
            },
            onMediaDelete: function (target) {
                deleteImage(target[0].src);
            }
        },
        dialogsInBody: true,
    });

    $('.textjawaban-essai').summernote({
        placeholder: 'Tulis Jawaban disini',
        tabsize: 2,
        toolbar: [
            //['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            //['fontname', ['fontname']],
            //['fontsize', ['fontsize']],
            //['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['math']],
            ['view', ['codeview']],
            //['cleaner',['cleaner']],
        ],
    });

    // Initialize static PG Kompleks editors if rendered from server.
    if ($('.textjawaban2').length) {
        initTextArea();
        bindPg2Checkboxes();
    }

    $("#picupload").on('change', function (e) {
        var form = new FormData($("#addfile")[0]);
        //console.log('nama file', names_files);
        //bank_id: id_bank, nomor: number, jenis: jenis_soal
        uploadAttach(base_url + 'cbtbanksoal/uploadfile?id_soal=' + idSoal, form);
    });

    $('#create').submit('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        jenis = $('#jenis-id').val();

        let formData = new FormData($('#create')[0]);
        formData.append('nomor_soal', nomor_soal)

        if (jenisSoal == '3') {
            if (!adaJawaban || !dataJodohkan) {
                Swal.fire({
                    title: "ERROR",
                    text: "JAWABAN tidak boleh kosong. Hubungkan minimal satu pasang pernyataan ke jawabannya.",
                    icon: "error",
                    showCancelButton: false
                });
                return;
            }
            // Append model and type
            if (dataJodohkan.model !== undefined) formData.append('model', dataJodohkan.model);
            if (dataJodohkan.type !== undefined) formData.append('type', dataJodohkan.type);
            // Append jawaban array
            var jawabanData = dataJodohkan.jawaban || [];
            if (Array.isArray(jawabanData)) {
                $.each(jawabanData, function (ks, vs) {
                    if (Array.isArray(vs)) {
                        $.each(vs, function (k, v) {
                            formData.append('jawaban[' + ks + '][' + k + ']', v == null ? '' : v);
                        });
                    }
                });
            }
        }
        // Handle Type 6: Urutan (Ordering)
        if (jenisSoal == '6') {
            var urutanData = getUrutanData();
            if (urutanData.items.length < 2) {
                Swal.fire({
                    title: "ERROR",
                    text: "Minimal 2 item diperlukan untuk soal urutan",
                    icon: "error",
                    showCancelButton: false
                });
                return;
            }
            formData.append('soal_opsi', JSON.stringify(urutanData));
            formData.append('jawaban', JSON.stringify(urutanData.correct_order));
        }
        // Handle Type 7: Cloze (Fill in the blanks)
        if (jenisSoal == '7') {
            // Get content directly from Summernote
            var clozeText = $('.textsoal').summernote('code');
            var clozeData = getClozeData();

            // Check if text is empty (strip tags to check real content)
            var cleanText = clozeText.replace(/<[^>]+>/g, '').trim();

            if (!cleanText || Object.keys(clozeData.blanks).length === 0) {
                Swal.fire({
                    title: "ERROR",
                    text: "Teks soal dan bagian rumpang (kunci jawaban) tidak boleh kosong",
                    icon: "error",
                    showCancelButton: false
                });
                return;
            }

            // Clean styling tags before saving
            // Create a temp div to manipulate HTML
            var $tempContent = $('<div>').html(clozeText);
            // Unwrap the content of .cloze-tag spans (removes the tag but keeps the text [[...]])
            $tempContent.find('.cloze-tag').each(function () {
                $(this).replaceWith($(this).text());
            });
            // Get the cleaned HTML
            var cleanSoal = $tempContent.html();

            // Ensure soal is set to clean content
            formData.set('soal', cleanSoal);

            formData.append('soal_opsi', JSON.stringify(clozeData));
            // Build jawaban object with correct answers
            var correctAnswers = {};
            Object.keys(clozeData.blanks).forEach(function (k) {
                correctAnswers[k] = clozeData.blanks[k].answer;
            });
            formData.append('jawaban', JSON.stringify(correctAnswers));
        }
        console.log(Object.fromEntries(formData))

        // Skip inputKosong validation for type 3 (menjodohkan), 6 (urutan), and 7 (cloze) - they have custom validation above
        if (jenisSoal != '3' && jenisSoal != '6' && jenisSoal != '7' && inputKosong()) {
            Swal.fire({
                title: "ERROR",
                text: "SOAL atau JAWABAN tidak boleh kosong",
                icon: "warning",
                showCancelButton: false
            });
        } else {
            $('#loading').removeClass('d-none');
            setTimeout(function () {
                $.ajax({
                    url: base_url + "cbtbanksoal/saveSoal",
                    type: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (data) {
                        $('#loading').addClass('d-none');
                        console.log(data);
                        if (!data || data.status === 'error' || data.status === false) {
                            Swal.fire({
                                title: "ERROR",
                                text: (data && data.message) ? data.message : "SOAL atau JAWABAN tidak boleh kosong",
                                icon: "warning",
                                showCancelButton: false
                            });
                        } else {
                            showSuccessToast("Soal nomor" + nomor_soal + " berhasil disimpan");
                            if (jenis === '1' || jenis === '2') {
                                $('#btn-' + jenis + nomor_soal).removeClass('btn-danger');
                                $('#btn-' + jenis + nomor_soal).addClass('btn-success');
                            } else {
                                $('#btn-' + jenis + nomor_soal).removeClass('btn-danger');
                                $('#btn-' + jenis + nomor_soal).addClass('btn-primary');
                            }
                        }
                    }, error: function (xhr, status, error) {
                        $('#loading').addClass('d-none');
                        console.log("error", xhr.responseText);
                        showDangerToast();
                    }
                });
            }, 500);
        }
    });

    $('#tambah-jawaban-pg2').on('click', function (e) {
        var opsi2 = $('#opsi-pg2');
        var count = opsi2.find('.pg-kompleks').length + 65;
        console.log(String.fromCharCode(count));

        var alphaCaps = String.fromCharCode(count);
        var lower = alphaCaps.toLowerCase();

        $('#opsi-pg2').append(buildPg2Card(alphaCaps, lower, '', false));

        initTextArea();
        bindPg2Checkboxes();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tab = $(e.target);
        var target = tab.attr("href");
        var id, jenis;
        if (target == '#ganda') {
            id = '11';
            jenis = '1';
        } else if (target == '#kompleks') {
            id = '21';
            jenis = '2';
        } else if (target == '#jodoh') {
            id = '31';
            jenis = '3';
        } else if (target == '#isian') {
            id = '41';
            jenis = '4';
        } else if (target == '#essai') {
            id = '51';
            jenis = '5';
        } else if (target == '#urutan') {
            id = '61';
            jenis = '6';
        } else if (target == '#cloze') {
            id = '71';
            jenis = '7';
        } else {
            console.log('id not defined');
        }

        getSoalById(idBank, 1, id, jenis);
    });

    var arrJenis = ['', 'PG', 'PG Kompleks', 'Menjodohkan', 'Isian Singkat', 'Uraian/essai'];
    $('#hapus-soal').on('click', function () {
        Swal.fire({
            title: "HAPUS ?",
            html: "Soal berikut akan dihapus<br>Nomor: <b>" + nomor_soal + "</b><br>Jenis: <b>" + arrJenis[jenis] + "</b>",
            icon: "error",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
        }).then(result => {
            if (result.value) {
                console.log($('#create').serialize());
                $.ajax({
                    url: base_url + "cbtbanksoal/hapussoal",
                    method: "POST",
                    data: $('#create').serialize(),
                    success: function (result) {
                        console.log("result", result);
                        var tit = result ? 'BERHASIL' : 'GAGAL';
                        var msg = result ? 'berhasil' : 'gagal';
                        var ic = result ? 'success' : 'error';
                        Swal.fire({
                            title: tit,
                            text: "Soal " + msg + " dihapus",
                            icon: ic,
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        showDangerToast('ERROR!!');
                    }
                });
            }
        })

    });

    if (inArray(1, adaPg)) {

    }
    applyKartuQuotaUI();
    getSoalById(idBank, nomor_soal, jenis + '' + nomor_soal, jenis);
});

function initTextArea() {
    $('.textjawaban2').summernote({
        placeholder: 'Tulis Jawaban disini',
        tabsize: 2,
        minHeight: 72,
        maxHeight: null,
        disableResizeEditor: true,
        toolbar: [
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            //['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['picture', 'math']],
            ['view', ['codeview']],
        ],
    });
}

function buildPg2Card(letterUpper, letterLower, content, isChecked, bsVal) {
    var checkedAttr = isChecked ? ' checked' : '';
    var checkedClass = isChecked ? ' is-checked' : '';
    var bsSel = (bsVal === 'B' || bsVal === 'S') ? String(bsVal).toUpperCase() : '';
    return '<div class="pg-kompleks mb-3' + checkedClass + '">' +
        '<div class="pg2-header">' +
        '  <div class="pg2-header-left">' +
        '    <span class="pg2-letter-badge abjad-badge">' + letterUpper + '</span>' +
        '    <span class="abjad-pg2"><b>Jawaban ' + letterUpper + '</b></span>' +
        '  </div>' +
        '  <div class="pg2-header-right">' +
        '    <span class="pg2-check-wrap">' +
        '      <span class="pg2-check-label">Jawaban Benar</span>' +
        '      <input class="pg2-checkbox check-pg2" type="checkbox" name="jawaban_benar_pg2[]" value="' + letterLower + '"' + checkedAttr + '>' +
        '    </span>' +
        '    <span class="pg2-bs-wrap d-none">' +
        '      <select class="pg2-bs-select form-select form-select-sm" data-letter="' + letterLower + '">' +
        '        <option value="">B/S</option>' +
        '        <option value="B"' + (bsSel === 'B' ? ' selected' : '') + '>Benar</option>' +
        '        <option value="S"' + (bsSel === 'S' ? ' selected' : '') + '>Salah</option>' +
        '      </select>' +
        '    </span>' +
        '    <button type="button" class="pg2-delete-btn" onclick="deleteOpsiPg2(this)" title="Hapus opsi">' +
        '      <i class="bi bi-trash"></i>' +
        '    </button>' +
        '  </div>' +
        '</div>' +
        '<div class="pg2-editor-wrap">' +
        '  <textarea class="textjawaban2" id="textjawaban2_' + letterLower + '" name="jawaban2_' + letterLower + '" placeholder="Tulis jawaban disini..." style="width:100%;">' + content + '</textarea>' +
        '</div>' +
        '</div>';
}

// Mode editor PG Kompleks: 'pgk' (multikunci) | 'pgkk' (Benar/Salah)
function setPg2Mode(mode) {
    var $root = $('#root-opsi-pg2');
    if (!$root.length) return;
    $root.toggleClass('pgkk-mode', mode === 'pgkk');
    $root.find('.pg2-mode-btn').each(function () {
        $(this).toggleClass('active btn-primary', $(this).data('pg2Mode') === mode);
        $(this).toggleClass('btn-outline-primary', $(this).data('pg2Mode') !== mode);
    });
    updatePg2CheckedCount();
}

function bindPg2Checkboxes() {
    $('#opsi-pg2').off('change', '.check-pg2').on('change', '.check-pg2', function () {
        var $card = $(this).closest('.pg-kompleks');
        if ($(this).is(':checked')) {
            $card.addClass('is-checked');
        } else {
            $card.removeClass('is-checked');
        }
        updatePg2CheckedCount();
    });
    $('#opsi-pg2').off('change', '.pg2-bs-select').on('change', '.pg2-bs-select', function () {
        var $card = $(this).closest('.pg-kompleks');
        if ($(this).val()) {
            $card.addClass('is-checked');
        } else {
            $card.removeClass('is-checked');
        }
        updatePg2CheckedCount();
    });
    updatePg2CheckedCount();
}

function updatePg2CheckedCount() {
    var $root = $('#root-opsi-pg2');
    var pgkkMode = $root.hasClass('pgkk-mode');
    var checkedCount = pgkkMode
        ? $('#opsi-pg2 .pg2-bs-select').filter(function () { return !!$(this).val(); }).length
        : $('#opsi-pg2 .check-pg2:checked').length;
    var $counter = $('#pg2-checked-count');
    if ($counter.length) {
        $counter.text(checkedCount);
        var $badge = $('#pg2-count-badge');
        if (checkedCount > 0) {
            $badge.removeClass('bg-light border').addClass('bg-success bg-opacity-10');
        } else {
            $badge.addClass('bg-light border').removeClass('bg-success bg-opacity-10');
        }
    }
}

function selectPgAnswer(letter) {
    var normalized = (letter || '').toString().trim().toLowerCase();

    // Legacy UI state
    $('.pg-opsi-card').removeClass('is-correct');

    // New UI state
    $('.qs-option-card').removeClass('is-correct');
    $('.qs-radio-custom').prop('checked', false);

    if (normalized) {
        // Legacy selectors
        $('.pg-opsi-card[data-opsi="' + normalized + '"]').addClass('is-correct');
        $('input.pg-radio[value="' + normalized + '"]').prop('checked', true);

        // New selectors
        $('.qs-option-card[data-opsi="' + normalized + '"]').addClass('is-correct');
        $('input.qs-radio-custom[value="' + normalized + '"]').prop('checked', true);

        // Shared hidden dropdown (server payload source)
        var arrAbjad = ['a', 'b', 'c', 'd', 'e'];
        $('#jawaban').prop('selectedIndex', arrAbjad.indexOf(normalized) + 1).change();

        // Legacy badge
        $('#pg-answer-text').text('Jawaban benar: ' + normalized.toUpperCase());
        $('#pg-answer-icon').removeClass('text-muted').addClass('text-success');
        $('#pg-answer-badge').removeClass('bg-light border').addClass('bg-success bg-opacity-10');

        // New badge
        $('#pg-answer-status').html('<i class="bi bi-check-circle-fill me-1 text-success"></i>Jawaban: ' + normalized.toUpperCase());
    } else {
        $('input.pg-radio').prop('checked', false);
        $('#pg-answer-text').text('Belum memilih jawaban');
        $('#pg-answer-icon').addClass('text-muted').removeClass('text-success');
        $('#pg-answer-badge').addClass('bg-light border').removeClass('bg-success bg-opacity-10');
        $('#pg-answer-status').html('<i class="bi bi-check-circle me-1"></i>Belum memilih jawaban');
    }
}

function myOwnCallBack(file, idtextarea) {
    var name = $('#textsoal').attr('data-name');
    var hash = $('#textsoal').attr('data-id');
    let data = new FormData();
    data.append("file", file);
    data.append(name, hash);

    $.ajax({
        data: data,
        type: "POST",
        url: base_url + "cbtbanksoal/upload_image",
        //url: "file-uploader.php", //Your own back-end uploader
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () { //Handle progress upload
            let myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) myXhr.upload.addEventListener('progress', progressHandlingFunction, false);
            return myXhr;
        },
        success: function (reponse) {
            if (reponse.status === true) {
                let listMimeImg = ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg'];
                let listMimeAudio = ['audio/mpeg', 'audio/ogg', 'udio/mp3'];
                let listMimeVideo = ['video/mpeg', 'video/mp4', 'video/webm'];
                let elem;

                if (listMimeImg.indexOf(file.type) > -1) {
                    //Picture
                    $(idtextarea).summernote('editor.insertImage', base_url + reponse.filename);
                } else if (listMimeAudio.indexOf(file.type) > -1) {
                    //Audio
                    elem = document.createElement("audio");
                    elem.setAttribute("src", base_url + reponse.filename);
                    elem.setAttribute("controls", "controls");
                    elem.setAttribute("preload", "metadata");
                    $(idtextarea).summernote('editor.insertNode', elem);
                } else if (listMimeVideo.indexOf(file.type) > -1) {
                    //Video
                    elem = document.createElement("video");
                    elem.setAttribute("src", base_url + reponse.filename);
                    elem.setAttribute("controls", "controls");
                    elem.setAttribute("preload", "metadata");
                    $(idtextarea).summernote('editor.insertNode', elem);
                } else {
                    //Other file type
                    elem = document.createElement("a");
                    let linkText = document.createTextNode(file.name);
                    elem.appendChild(linkText);
                    elem.title = file.name;
                    elem.href = base_url + reponse.filename;
                    $(idtextarea).summernote('editor.insertNode', elem);
                }
            }
        }
    });
}

function progressHandlingFunction(e) {
    if (e.lengthComputable) {
        //Log current progress
        console.log((e.loaded / e.total * 100) + '%');

        //Reset progress on complete
        if (e.loaded === e.total) {
            console.log("Upload finished.");
        }
    }
}

function uploadImage(image, editor, editable) {
    var name = $('#textsoal').attr('data-name');
    var hash = $('#textsoal').attr('data-id');

    var data = new FormData();
    data.append("file", image);
    data.append(name, hash);

    $.ajax({
        url: base_url + "cbtbanksoal/upload_image",
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        type: "POST",
        success: function (data) {
            $(idtextarea).summernote("insertImage", data);
        },
        error: function (data) {
            console.log(data);
        }
    });
}

function deleteImage(src) {
    var $name = $('#textsoal').attr('data-name');
    var hash = $('#textsoal').attr('data-id');
    var data = new FormData();
    data.append("src", src);
    data.append(name, hash);

    $.ajax({
        data: { src: src },
        type: "POST",
        url: base_url + "cbtbanksoal/delete_image",
        cache: false,
        success: function (response) {
            console.log(response);
        }
    });
}

function inputKosong() {
    var kosong = true;
    if (jenis === '1') {
        $('.textjawaban').each(function () {
            if ($(this).summernote('isEmpty')) {
                kosong = true;
                return false;
            } else {
                kosong = false;
            }
        });
    } else if (jenis === '2') {
        $('.textjawaban2').each(function () {
            if ($(this).summernote('isEmpty')) {
                kosong = true;
                return false;
            } else {
                kosong = false;
            }
        });
    } else if (jenis === '3') {
        kosong = false;
    } else if (jenis === '4') {
        kosong = $('#jawaban-isian').val().trim() == "";
    } else {
        kosong = $('.textjawaban-essai').summernote('isEmpty');
    }
    return kosong;
}

function inArray(val, array) {
    var found = $.inArray(val, array);
    return found >= 0;
}

var old_id = 11;

function getKartuPlanItem(jenis, nomor) {
    var workflow = window.BANK_WORKFLOW || {};
    var byJenis = workflow.kartuPlanByJenis || {};
    var items = byJenis[String(jenis)] || byJenis[jenis] || [];
    for (var i = 0; i < items.length; i++) {
        if (parseInt(items[i].nomor || 0, 10) === parseInt(nomor || 0, 10)) {
            return items[i];
        }
    }
    return null;
}

function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
}

function getJenisLabel(jenis) {
    var labels = {
        1: 'Pilihan Ganda',
        2: 'PG Kompleks',
        3: 'Menjodohkan',
        4: 'Isian Singkat',
        5: 'Essai',
        6: 'Urutan',
        7: 'Cloze'
    };
    return labels[jenis] || ('Jenis ' + jenis);
}

function shortenText(text, maxLen) {
    var raw = String(text || '').trim();
    if (raw.length <= maxLen) return raw;
    return raw.substring(0, Math.max(0, maxLen - 3)) + '...';
}

function getExistingNomorByJenis(jenis) {
    switch (parseInt(jenis, 10)) {
        case 1: return Array.isArray(adaPg) ? adaPg : [];
        case 2: return Array.isArray(adaPg2) ? adaPg2 : [];
        case 3: return Array.isArray(adaJodohkan) ? adaJodohkan : [];
        case 4: return Array.isArray(adaIsian) ? adaIsian : [];
        case 5: return Array.isArray(adaEssai) ? adaEssai : [];
        case 6: return Array.isArray(adaUrutan) ? adaUrutan : [];
        case 7: return Array.isArray(adaCloze) ? adaCloze : [];
        default: return [];
    }
}

function renderKartuPlanHint(jenis, nomor) {
    var box = $('#kartu-plan-hint');
    if (!box.length) return;

    var workflow = window.BANK_WORKFLOW || {};
    var quotaByJenis = workflow.kartuQuotaByJenis || {};
    var quota = quotaByJenis[String(jenis)] || quotaByJenis[jenis] || null;
    var planItem = getKartuPlanItem(jenis, nomor);

    if (!quota && !planItem) {
        box.hide().html('');
        return;
    }

    var quotaText = '';
    if (quota) {
        quotaText = '<span class="badge bg-light text-dark border me-2">Rencana: ' + (quota.planned || 0) +
            ' | Terisi: ' + (quota.existing || 0) + ' | Sisa: ' + (quota.remaining || 0) + '</span>';
    }

    if (!planItem) {
        box.removeClass('alert-warning').addClass('alert-light');
        box.html('<div class="small">' + quotaText + 'Tidak ada item rencana spesifik untuk nomor soal ini pada Kartu Soal.</div>');
        box.show();
        return;
    }

    var meta = [];
    if (planItem.level_kognitif) meta.push('Level: ' + escapeHtml(planItem.level_kognitif));
    if (planItem.materi) meta.push('Materi: ' + escapeHtml(planItem.materi));
    if (planItem.cp) meta.push('CP: ' + escapeHtml(planItem.cp));

    var detail = '<div class="small text-muted mt-1">' + meta.join(' | ') + '</div>';
    var indikator = planItem.indikator_soal ? ('<div class="small mt-1"><strong>Indikator:</strong> ' + escapeHtml(planItem.indikator_soal) + '</div>') : '';

    box.removeClass('alert-light').addClass('alert-warning');
    box.html(
        '<div><strong>Rencana Kartu Soal #' + nomor + '</strong> ' + quotaText + '</div>' +
        '<div class="small mt-1"><strong>Tujuan:</strong> ' + escapeHtml(planItem.tujuan || '-') + '</div>' +
        detail +
        indikator
    );
    box.show();
}

function renderKartuPlanSummary(jenis, nomorAktif) {
    var card = $('#kartu-plan-summary-card');
    var wrap = $('#kartu-plan-summary');
    if (!card.length || !wrap.length) return;

    var workflow = window.BANK_WORKFLOW || {};
    var byJenis = workflow.kartuPlanByJenis || {};
    var items = byJenis[String(jenis)] || byJenis[jenis] || [];

    if (!items.length) {
        card.hide();
        wrap.html('');
        return;
    }

    var quotaByJenis = workflow.kartuQuotaByJenis || {};
    var quota = quotaByJenis[String(jenis)] || quotaByJenis[jenis] || {};
    var existingNomor = getExistingNomorByJenis(jenis);
    var totalTerisi = 0;
    var totalBelum = 0;

    for (var i0 = 0; i0 < items.length; i0++) {
        var n0 = parseInt(items[i0].nomor || 0, 10);
        if (inArray(n0, existingNomor)) totalTerisi++;
        else totalBelum++;
    }

    var head = '<div class="small text-muted mb-2">' +
        '<strong>' + escapeHtml(getJenisLabel(parseInt(jenis, 10))) + '</strong> ' +
        '<span class="badge bg-light text-dark border ms-1">Rencana ' + (quota.planned || items.length) + '</span> ' +
        '<span class="badge bg-success-subtle text-success border ms-1">Terisi ' + totalTerisi + '</span>' +
        '<span class="badge bg-secondary-subtle text-secondary border ms-1">Belum ' + totalBelum + '</span>' +
        '</div>';

    var list = '<div class="d-flex flex-wrap gap-2">';
    for (var i = 0; i < items.length; i++) {
        var it = items[i] || {};
        var nomor = parseInt(it.nomor || 0, 10);
        var isActive = parseInt(nomorAktif || 0, 10) === nomor;
        var isFilled = inArray(nomor, existingNomor);
        var tujuan = shortenText(it.tujuan || '-', 80);
        var btnClass = isActive ? 'btn-primary' : (isFilled ? 'btn-outline-success' : 'btn-outline-secondary');
        var title = 'No. ' + nomor + ' - ' + (it.tujuan || '-');
        var badgeStatus = isFilled
            ? '<span class="badge bg-success-subtle text-success border ms-1">Terisi</span>'
            : '<span class="badge bg-secondary-subtle text-secondary border ms-1">Belum</span>';
        list += '<button type="button" class="btn btn-sm ' + btnClass + '" ' +
            'onclick="jumpToKartuPlanItem(' + parseInt(jenis, 10) + ',' + nomor + ')" ' +
            'title="' + escapeHtml(title) + '">' +
            '<span class="fw-semibold me-1">#' + nomor + '</span>' + escapeHtml(tujuan) + badgeStatus +
            '</button>';
    }
    list += '</div>';

    wrap.html(head + list);
    card.show();
}

function jumpToKartuPlanItem(jenis, nomor) {
    var id = '' + jenis + nomor;
    getSoalById(idBank, nomor, id, String(jenis));
}

function focusNomorButton(jenis, nomor) {
    var btnId = '#btn-' + String(jenis) + String(nomor);
    var $btn = $(btnId);
    if (!$btn.length) return;

    try {
        $btn.get(0).scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center'
        });
    } catch (e) {
        // Fallback for older browsers
        $('html, body').animate({ scrollTop: Math.max(0, $btn.offset().top - 180) }, 250);
    }

    $btn.stop(true, true).fadeOut(70).fadeIn(70).fadeOut(70).fadeIn(70);
}

function applyKartuQuotaUI() {
    var workflow = window.BANK_WORKFLOW || {};
    var quotaByJenis = workflow.kartuQuotaByJenis || {};

    $('.btn-add-soal').each(function () {
        var $btn = $(this);
        var jenis = parseInt($btn.data('jenis') || '0', 10);
        if (!jenis) return;

        var quota = quotaByJenis[String(jenis)] || quotaByJenis[jenis];
        if (!quota) return;

        var sisa = parseInt(quota.remaining || 0, 10);
        var planned = parseInt(quota.planned || 0, 10);
        var existing = parseInt(quota.existing || 0, 10);
        var badge = $('#quota-badge-' + jenis);

        if (badge.length) {
            badge.text('Rencana ' + planned + ' | Terisi ' + existing + ' | Sisa ' + sisa);
            if (sisa <= 0 && planned > 0) {
                badge.removeClass('bg-light text-dark').addClass('bg-warning text-dark');
            }
        }

        var defaultHtml = $btn.data('default-html');
        if (!defaultHtml) {
            $btn.data('default-html', $btn.html());
            defaultHtml = $btn.html();
        }

        if (planned > 0 && sisa <= 0) {
            $btn.addClass('disabled').attr('aria-disabled', 'true');
            $btn.html('<i class="bi bi-check2-all"></i> Kuota Penuh');
        } else {
            $btn.removeClass('disabled').removeAttr('aria-disabled');
            $btn.html(defaultHtml);
        }
    });
}

function getKartuPlanItem(jenis, nomor) {
    var workflow = window.BANK_WORKFLOW || {};
    var byJenis = workflow.kartuPlanByJenis || {};
    var items = byJenis[String(jenis)] || byJenis[jenis] || [];
    for (var i = 0; i < items.length; i++) {
        var itemNomor = parseInt(items[i].nomor || 0, 10);
        var targetNomor = parseInt(nomor || 0, 10);
        if (itemNomor === targetNomor || (i + 1) === targetNomor) {
            return items[i];
        }
    }
    return null;
}

function updateMetadataTextDisplay(tujuanId, topik, indikator, kesulitan, tujuanTextOpt) {
    $('#tujuan_id').val(tujuanId || '');
    $('#topik').val(topik || '');
    $('#indikator').val(indikator || '');
    $('#kesulitan').val(kesulitan || 'C4');

    if (tujuanId && tujuanId !== '0') {
        $('#select_tp_id').val(String(tujuanId));
    } else {
        $('#select_tp_id').val('');
    }

    const kisiItemId = $('#kartu_item_id').val();
    if (kisiItemId && kisiItemId !== '0') {
        $('#tp-select-wrapper').addClass('d-none');
        $('#tp-display-wrapper').removeClass('d-none');
    } else {
        $('#tp-select-wrapper').removeClass('d-none');
        $('#tp-display-wrapper').addClass('d-none');
    }

    var tujuanText = tujuanTextOpt || '-';
    if ((!tujuanTextOpt || tujuanTextOpt === '-') && tujuanId) {
        var $opt = $('#select_tp_id option[value="' + tujuanId + '"]');
        if ($opt.length) {
            tujuanText = $opt.text();
        }
    }
    $('#meta-tujuan-text').text(tujuanText || '-');
    $('#meta-topik-text').text(topik || '-');
    $('#meta-indikator-text').text(indikator || '-');

    var kesulitanMap = {
        'C1': 'C1 - Mengingat',
        'C2': 'C2 - Memahami',
        'C3': 'C3 - Menerapkan',
        'C4': 'C4 - Menganalisis',
        'C5': 'C5 - Mengevaluasi',
        'C6': 'C6 - Mencipta'
    };
    $('#meta-kesulitan-text').text(kesulitanMap[kesulitan] || (kesulitan || 'C4 - Menganalisis'));
}

function resetMetadataTextDisplay() {
    $('#kartu_item_id').val('');
    $('#tujuan_id').val('');
    $('#topik').val('');
    $('#indikator').val('');
    $('#kesulitan').val('C4');
    $('#select_tp_id').val('');
    $('#tp-select-wrapper').removeClass('d-none');
    $('#tp-display-wrapper').addClass('d-none');
    $('#meta-tujuan-text').text('-');
    $('#meta-topik-text').text('-');
    $('#meta-indikator-text').text('-');
    $('#meta-kesulitan-text').text('C4 - Menganalisis');
}

function autoFillMetadataFromKartu(jenis_soal, number) {
    var planItem = getKartuPlanItem(jenis_soal, number);
    if (!planItem) return;

    var tujuanId = '';
    var kesulitan = 'C4';

    if (planItem.id_tujuan && parseInt(planItem.id_tujuan, 10) > 0) {
        tujuanId = planItem.id_tujuan;
    }

    if (planItem.level_kognitif) {
        var raw = String(planItem.level_kognitif).toUpperCase();
        if (raw.indexOf('C1') !== -1) kesulitan = 'C1';
        else if (raw.indexOf('C2') !== -1) kesulitan = 'C2';
        else if (raw.indexOf('C3') !== -1) kesulitan = 'C3';
        else if (raw.indexOf('C4') !== -1) kesulitan = 'C4';
        else if (raw.indexOf('C5') !== -1) kesulitan = 'C5';
        else if (raw.indexOf('C6') !== -1) kesulitan = 'C6';
    }

    if (planItem.id_item && parseInt(planItem.id_item, 10) > 0) {
        $('#kartu_item_id').val(planItem.id_item);
        $('#unlink_kisi').val('0');
        $('#alert-kisi-tautan').removeClass('d-none');

        var judulKisi = 'No.' + (planItem.nomor || number);
        var kisiDesc = planItem.indikator_soal || planItem.tujuan || '';
        if (kisiDesc.trim() !== '') {
            judulKisi += ' - ' + kisiDesc.trim().substring(0, 60) + (kisiDesc.length > 60 ? '...' : '');
        }
        $('#text-kisi-tautan').text('Soal ini ditautkan ke Kisi-kisi: ' + judulKisi);

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                icon: 'info', title: 'Metadata Kisi-kisi No.' + number + ' diisi otomatis.'
            });
        }
    }

    const fullTujuanText = (planItem.tujuan_kode ? (planItem.tujuan_kode + ' - ') : '') + (planItem.tujuan || '');
    updateMetadataTextDisplay(
        tujuanId,
        planItem.materi || '',
        planItem.indikator_soal || '',
        kesulitan,
        fullTujuanText
    );
}

function getSoalById(id_bank, number, id, jenis_soal) {
    jenisSoal = jenis_soal;
    nomor_soal = number;
    jenis = jenis_soal;
    $('#empty-soal').addClass('d-none');
    $('#not-empty-soal').addClass('d-none');
    renderKartuPlanHint(jenis_soal, number);
    renderKartuPlanSummary(jenis_soal, number);
    focusNomorButton(jenis_soal, number);

    $('#btn-' + old_id).removeClass('btn-active-soal active');
    $('#btn-' + id).addClass('btn-active-soal active');
    $('#jenis-id').val(jenis_soal).trigger('change');
    if (jenis_soal === '1') {
        $('#nomor-soal').html('<b>Soal PG Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('d-none col-md-12').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12').addClass('col-md-6');
        $('#root-opsi-cloze').addClass('d-none');
        $('#root-opsi-urutan').addClass('d-none');

        // 1 enable
        $("#pg-options-section, #root-opsi-pg").removeClass('d-none');
        $('.textjawaban').removeAttr('disabled', 'disabled');
        $('#jawaban-benar').removeClass('d-none');
        $('#root-jawaban-pg').removeClass('d-none');
        $('#jawaban').prop('required', true);
        $('#jawaban').removeAttr('disabled');

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#model-opsi').attr('disabled', 'disabled');
        $('#type-opsi').attr('disabled', 'disabled');

        // 4 disable
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
    } else if (jenis_soal === '2') {
        $('#nomor-soal').html('<b>Soal Kompleks Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('d-none col-md-12').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12').addClass('col-md-6');
        $('#root-opsi-cloze').addClass('d-none');
        $('#root-opsi-urutan').addClass('d-none');

        // 1 disable
        $("#pg-options-section, #root-opsi-pg").addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#jawaban-benar').addClass('d-none');
        $('#root-jawaban-pg').addClass('d-none');
        $('#jawaban').prop('required', false);
        $('#jawaban').attr('disabled', 'disabled');

        // 2 enable
        $('#root-opsi-pg2').removeClass('d-none');
        $('.textjawaban2').removeAttr('disabled');
        $('.check-pg2').removeAttr('disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#model-opsi').attr('disabled', 'disabled');
        $('#type-opsi').attr('disabled', 'disabled');

        // 4 disable
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
    } else if (jenis_soal === '3') {
        $('#nomor-soal').html('<b>Soal Menjodohkan Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('d-none col-md-6').addClass('col-md-12');
        $('#jawaban-area').removeClass('col-md-6').addClass('col-md-12');
        $('#root-opsi-cloze').addClass('d-none');
        $('#root-opsi-urutan').addClass('d-none');

        // 1 disable
        $("#pg-options-section, #root-opsi-pg").addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('#jawaban').prop('required', false);
        $('#jawaban').attr('disabled', 'disabled');

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 enable
        $('#root-opsi-jodohkan').removeClass('d-none');
        $('#model-opsi').removeAttr('disabled');
        $('#type-opsi').removeAttr('disabled');

        // 4 disable
        $('#jawaban-benar').addClass('d-none');
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
    } else if (jenis_soal === '4') {
        $('#nomor-soal').html('<b>Soal Isian Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('d-none col-md-12').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12').addClass('col-md-6');
        $('#root-opsi-cloze').addClass('d-none');
        $('#root-opsi-urutan').addClass('d-none');

        // 1 disable
        $("#pg-options-section, #root-opsi-pg").addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('#jawaban').prop('required', false);
        $('#jawaban').attr('disabled', 'disabled');

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#model-opsi').attr('disabled', 'disabled');
        $('#type-opsi').attr('disabled', 'disabled');

        // 4 enable
        $('#jawaban-benar').removeClass('d-none');
        $('#root-jawaban-isian').removeClass('d-none');
        $('#jawaban-isian').removeAttr('disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
    } else if (jenis_soal === '5') {
        $('#nomor-soal').html('<b>Soal Essai Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('d-none col-md-12').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12').addClass('col-md-6');


        // 1 disable
        $("#pg-options-section, #root-opsi-pg").addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('#jawaban').prop('required', false);
        $('#jawaban').attr('disabled', 'disabled');

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');

        // 4 disable
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 enable
        $('#jawaban-benar').removeClass('d-none');
        $('#root-jawaban-essai').removeClass('d-none');
        $('#jawaban-essai').removeAttr('disabled');

        // 6 disable
        $('#root-opsi-urutan').addClass('d-none');
        // 7 disable
        $('#root-opsi-cloze').addClass('d-none');
    } else if (jenis_soal === '6') {
        $('#nomor-soal').html('<b>Soal Urutan Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('d-none col-md-12').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12').addClass('col-md-6');
        $('#root-opsi-cloze').addClass('d-none');

        // Disable all other options
        $("#pg-options-section, #root-opsi-pg").addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('#jawaban').prop('required', false);
        $('#jawaban').attr('disabled', 'disabled');
        $('#root-opsi-pg2').addClass('d-none');
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#root-jawaban-isian').addClass('d-none');
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-benar').addClass('d-none');
        $('#root-opsi-cloze').addClass('d-none');

        // 6 enable
        $('#root-opsi-urutan').removeClass('d-none');
    } else if (jenis_soal === '7') {
        $('#nomor-soal').html('<b>Soal Isian Rumpang Nomor: ' + number + '</b>');

        // Show Summernote editor for cloze
        $('#soal-area').removeClass('d-none col-md-12').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12').addClass('col-md-6');

        // Disable all other options
        $("#pg-options-section, #root-opsi-pg").addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('#jawaban').prop('required', false);
        $('#jawaban').attr('disabled', 'disabled');
        $('#root-opsi-pg2').addClass('d-none');
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#root-jawaban-isian').addClass('d-none');
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-benar').addClass('d-none');
        $('#root-opsi-urutan').addClass('d-none');

        // 7 enable
        $('#root-opsi-cloze').removeClass('d-none');
    }

    if (typeof syncSplitLayout === 'function') {
        syncSplitLayout();
    }

    // Update editor title with current question number
    $('#editor-nomor').text('#' + number);

    // Update AI replace checkbox label with current question number
    $('#ai-replace-nomor').text(number);

    $('#loading').removeClass('d-none');
    setTimeout(function () {
        old_id = id;
        nomor_soal = number;
        jenis = jenis_soal;
        //console.log("data", bank_soal);
        $.ajax({
            type: "GET",
            url: base_url + "cbtbanksoal/getSoalByNomor",
            dataType: "JSON",
            data: { bank_id: id_bank, nomor: number, jenis: jenis_soal },
            //responseType: "blob",
            success: function (data) {
                $('#loading').addClass('d-none');
                console.log('soal', data);
                if (data == null || !data.id_soal) {
                    $('#empty-soal').addClass('d-none'); // Ensure empty state is hidden if we are manually adding
                    $('#not-empty-soal').removeClass('d-none');
                    $('#soal-id').val('0');
                    $('#method').val('add');
                    nomor_soal = number; // Update global state
                    jenisSoal = String(jenis_soal); // Update global state
                    $('#editor-nomor').text('#' + number);

                    // Reset metadata display
                    resetMetadataTextDisplay();

                    $('#kartu_item_id').val('');
                    $('#unlink_kisi').val('0');
                    $('#alert-kisi-tautan').addClass('d-none');
                    $('#textsoal').summernote('code', '');
                    $(".textjawaban").each(function () {
                        $(this).summernote('code', '');
                    });
                    $('#jawaban').prop('selectedIndex', 0).change();
                    selectPgAnswer(''); // Clear visual radio state
                    $('#jawaban-essai').summernote('code', '');

                    // Reset specialized inputs
                    if (jenis_soal === '3') {
                        dataJodohkan = {
                            jawaban: [],
                            model: '1',
                            type: '2'
                        };
                        adaJawaban = false;
                        var kontenKosong = $('#jawaban-jodohkan');
                        if (kontenKosong.length && typeof kontenKosong.linkerList === 'function') {
                            kontenKosong.html('');
                            kontenKosong.linkerList({
                                data: dataJodohkan,
                                id: nomor_soal,
                                viewMode: '3',
                                callback: function (id, data, hasLinks) {
                                    dataJodohkan = data;
                                    adaJawaban = hasLinks;
                                }
                            });
                        }
                    }
                    if (jenis_soal === '6') resetUrutanItems();
                    if (jenis_soal === '7') {
                        updateClozePreview();
                        updateClozeAnswers();
                    }

                    // === AUTO-FILL METADATA DARI KARTU SOAL ===
                    // Setelah reset, isi metadata otomatis dari kartu_plan_by_jenis
                    autoFillMetadataFromKartu(jenis_soal, number);
                } else {
                    $('#empty-soal').addClass('d-none');
                    $('#not-empty-soal').removeClass('d-none');
                    $('#soal-id').val(data.id_soal);
                    $('#method').val('edit');

                    if (data.bobot_irt != null && data.bobot_irt !== '') {
                        $('#bobot_irt').val(data.bobot_irt);
                    } else {
                        $('#bobot_irt').val('1.0');
                    }

                    if (data.kartu_item_id) {
                        $('#kartu_item_id').val(data.kartu_item_id);
                        $('#unlink_kisi').val('0');
                        $('#alert-kisi-tautan').removeClass('d-none');
                        $('#text-kisi-tautan').text('Soal ini ditautkan ke Kisi-kisi: ' + (data.kartu_judul || 'Kartu Soal'));
                    } else {
                        $('#kartu_item_id').val('');
                        $('#unlink_kisi').val('0');
                        $('#alert-kisi-tautan').addClass('d-none');
                    }

                    // Update metadata display with combined code and text
                    const displayTujuanText = (data.tujuan_kode ? (data.tujuan_kode + ' - ') : '') + (data.tujuan_text || '');
                    updateMetadataTextDisplay(
                        data.tujuan_id || '',
                        data.topik || '',
                        data.indikator || '',
                        data.kesulitan || 'C4',
                        displayTujuanText
                    );

                    if (data.kartu_item_id) {
                        if (typeof syncMetadataFromLinkedKisi === 'function') {
                            var shouldForceSyncKisi = (
                                !String($('#tujuan_id').val() || '').trim() ||
                                !String($('#topik').val() || '').trim() ||
                                !String($('#indikator').val() || '').trim() ||
                                !String($('#kesulitan').val() || '').trim()
                            );
                            syncMetadataFromLinkedKisi(data.kartu_item_id, jenis_soal, {
                                force: shouldForceSyncKisi,
                                silent: true
                            });
                        }
                    } else {
                        // === AUTO-FILL METADATA DARI KARTU SOAL ===
                        // Isi metadata otomatis dari kartu_plan_by_jenis jika belum ditautkan
                        // Force check for all types
                        autoFillMetadataFromKartu(jenis_soal, number);
                    }

                    var checkSoal = data.soal == null ? '' : data.soal;
                    var sSoal = $($.parseHTML(checkSoal));
                    sSoal.find(`img`).each(function () {
                        var curSrc = $(this).attr('src');
                        if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
                        } else {
                            var pathUpload = 'uploads';
                            var forReplace = curSrc.split(pathUpload);
                            $(this).attr('src', base_url + pathUpload + forReplace[1]);
                        }
                        $(this).removeAttr('alt');
                    });

                    sSoal.find(`video`).each(function () {
                        var curSrc = $(this).attr('src');
                        if (curSrc.indexOf("base64") > 0) {
                        } else {
                            var pathUpload = 'uploads';
                            var forReplace = curSrc.split(pathUpload);
                            $(this).attr('src', base_url + pathUpload + forReplace[1]);
                        }
                    });

                    sSoal.find(`audio`).each(function () {
                        var curSrc = $(this).attr('src');
                        if (curSrc.indexOf("base64") > 0) {
                        } else {
                            var pathUpload = 'uploads';
                            var forReplace = curSrc.split(pathUpload);
                            $(this).attr('src', base_url + pathUpload + forReplace[1]);
                        }
                    });

                    // Auto-highlight existing cloze blanks if any
                    if (jenis_soal === '7') {
                        var tempDiv = $('<div>').append(sSoal);
                        // Regex text nodes only to avoid breaking HTML tags
                        // However, simplest robust way for Summernote content is replace on HTML string if pattern is simple
                        // We use a safe replace that looks for [[...]] not inside attributes (basic assumption)
                        var html = tempDiv.html();
                        // Check if already styled (avoid double wrapping)
                        if (html.indexOf('cloze-tag') === -1) {
                            html = html.replace(/\[\[([^\]]+)\]\]/g, function (match, content) {
                                // Don't format if it looks like it's already inside a tag or is a tag (rudimentary check)
                                return '<span class="cloze-tag" style="background-color:#fff3cd; padding:2px 6px; border-radius:4px; border:1px solid #ffe69c; color:#664d03; font-weight:bold;">[[' + content + ']]</span>';
                            });
                            sSoal = $($.parseHTML(html));
                        }
                    }

                    $('#textsoal').summernote('code', sSoal);

                    if (jenis_soal === '1') {
                        let arrJawaban = [data.opsi_a, data.opsi_b, data.opsi_c, data.opsi_d, data.opsi_e];
                        let arrAbjad = ['a', 'b', 'c', 'd', 'e'];

                        $.each(arrJawaban, function (i, item) {
                            var chekJawaban = arrJawaban[i] == null ? '' : arrJawaban[i];
                            var sJawabPg = $($.parseHTML(chekJawaban));
                            sJawabPg.find(`img`).each(function () {
                                $(this).removeAttr('alt');
                                var curSrc = $(this).attr('src');
                                if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
                                } else {
                                    var pathUpload = 'uploads';
                                    var forReplace = curSrc.split(pathUpload);
                                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                                }
                            });

                            $('#textjawaban_' + arrAbjad[i]).summernote('code', sJawabPg);
                        });

                        var jwb = data.jawaban == null ? '' : $.trim((data.jawaban).toLowerCase());
                        console.log(jwb);
                        $('#jawaban').prop('selectedIndex', arrAbjad.indexOf(jwb) + 1).change();
                        selectPgAnswer(jwb); // Sync visual radio state
                    } else if (jenis_soal === '2') {
                        var pgkkMode = data.tipe_soal === 'pgkk';
                        if (pgkkMode) setPg2Mode('pgkk'); else setPg2Mode('pgk');
                        var jwb2 = '';
                        if (data.opsi_a) {
                            $.each(data.opsi_a, function (k, v) {
                                var checked = '';
                                var bsVal = '';
                                if (pgkkMode) {
                                    var bsv = (data.jawaban && typeof data.jawaban === 'object')
                                        ? (data.jawaban[k] || data.jawaban[k.toUpperCase()] || '')
                                        : '';
                                    bsv = String(bsv || '').toUpperCase();
                                    if (bsv === 'B' || bsv === 'S') bsVal = bsv;
                                } else if (inArray(k, data.jawaban)) {
                                    checked = 'checked="checked"';
                                }
                                var htmlContent = v; // Default to raw content
                                if (v && typeof v === 'string' && (v.indexOf('<') !== -1 || v.indexOf('&lt;') !== -1)) {
                                    // Only try looking for images if it looks like HTML
                                    try {
                                        var pv = $($.parseHTML(v));
                                        var hasImg = false;
                                        pv.find(`img`).each(function () {
                                            hasImg = true;
                                            var curSrc = $(this).attr('src');
                                            if (curSrc && (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads"))) {
                                            } else {
                                                var pathUpload = 'uploads';
                                                var forReplace = curSrc.split(pathUpload);
                                                if (forReplace.length > 1) {
                                                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                                                }
                                            }
                                        });

                                        if (hasImg) {
                                            // If we modified images, we need to reconstruct the HTML
                                            // Wrap in div to get innerHTML of everything including top-level nodes
                                            htmlContent = $('<div>').append(pv.clone()).html();
                                        }
                                    } catch (e) {
                                        console.log('Error parsing option HTML', e);
                                    }
                                }

                                jwb2 += buildPg2Card(k.toUpperCase(), k, htmlContent, !!checked, bsVal);
                            });
                        } else {
                            jwb2 += buildPg2Card('A', 'a', '', false, '');
                        }
                        $('#opsi-pg2').html(jwb2);
                        initTextArea();
                        bindPg2Checkboxes();
                    } else if (jenis_soal === '3') {
                        let konten = $('#jawaban-jodohkan')
                        konten.html('')
                        if (!data.jawaban) {
                            data.jawaban = {
                                jawaban: [],
                                model: '1',
                                type: '2'
                            }
                        }
                        dataJodohkan = data.jawaban;
                        konten.linkerList({
                            data: data.jawaban,
                            id: nomor_soal,
                            viewMode: '3',
                            callback: function (id, data, hasLinks) {
                                //console.log('data:'+id, data, hasLinks)
                                dataJodohkan = data
                                adaJawaban = hasLinks
                            }
                        });
                    } else if (jenis_soal === '4') {
                        $('#jawaban-isian').val($.trim(data.jawaban));
                    } else if (jenis_soal === '6') {
                        $('#urutan-items-container').empty();
                        urutanItemCount = 0;
                        var opsiks = data.opsi_a;
                        // Handle structure { items: [...] }
                        var items = (opsiks && opsiks.items) ? opsiks.items : opsiks;

                        if (items && items.length > 0) {
                            $.each(items, function (i, val) {
                                addUrutanItem();
                                $('.urutan-input').last().val(val);
                            });
                        } else {
                            resetUrutanItems();
                        }
                    } else if (jenis_soal === '7') {
                        updateClozeAnswers();

                        var jwbs = data.jawaban;
                        if (jwbs) {
                            $.each(jwbs, function (k, v) {
                                var ans = (typeof v === 'object' && v.answer) ? v.answer : v;
                                $('input[data-blank="' + k + '"]').val(ans);
                            });
                        }
                    } else {
                        var sJawabEssai = $($.parseHTML(`<div>${data.jawaban}</div>`));
                        $('#jawaban-essai').summernote('code', sJawabEssai);
                    }
                    let listMimeAudio = ['audio/mpeg', 'audio/ogg'];
                    let listMimeVideo = ['video/mpeg', 'video/mp4', 'video/webm'];

                    var audioPendukung = '';
                    var videoPendukung = '';

                    $.each(data.file, function (i, v) {
                        if (listMimeAudio.indexOf(v.type) > -1) {
                            audioPendukung += '<audio id="audio-att">' +
                                '    <source src="' + base_url + v.src + '" type="' + v.type + '" />' +
                                '  </audio>' +
                                '        <span>' + v.src + '</span>';
                        } else {
                            videoPendukung += '    <span>' + v.file_name +
                                '</span><br><video id="video-att" width="100" height="auto" src="' + base_url + v.src + '" controls></video>' +
                                '        <span>3 x</span>';
                        }
                    });

                    $('#audios').html(audioPendukung);
                    $('#videos').html(videoPendukung);

                    $('#audio-att, #video-att').stylise({
                        //enableSeeking: false,
                        enableRestart: false
                    });
                    $('[data-toggle="tooltip"]').tooltip();
                    if (typeof syncSplitLayout === 'function') {
                        syncSplitLayout();
                    }
                }

                /*
                $('.note-editable').find('p').each(function () {
                    if(HasArabicCharacters($(this))){
                        $(this).removeAttr('style');
                        $(this).attr('style', '"font-family: "uthmanic"; font-size: 22pt;"');
                    }

                    $(this).find('span').each(function () {
                        console.log(HasArabicCharacters($(this)));
                        if(HasArabicCharacters($(this))){
                            $(this).removeAttr('style');
                            $(this).attr('style', 'font-family: "uthmanic"; font-size: 22pt;');
                        }
                    });
                });
                */
            },
            error: function (e) {
                $('#loading').addClass('d-none');
                console.log("error", e.responseText);
                showDangerToast(e.responseText);
            }
        });
    }); // setTimeout closing
}

function requestTambahSoal(bank, nomor, jenis, tab) {
    $.ajax({
        url: base_url + "cbtbanksoal/tambahSoal",
        type: "POST",
        dataType: "JSON",
        data: {
            nomor: nomor,
            jenis: jenis,
            bank: bank
        },
        success: function (data) {
            if (data && data.status) {
                window.location.href = base_url + 'cbtbanksoal/buatsoal/' + bank + '?tab=' + tab + '&no=' + nomor;
                return;
            }
            Swal.fire({
                title: 'Peringatan',
                text: (data && data.message) ? data.message : 'Gagal menambah soal',
                icon: 'warning'
            });
        },
        error: function (xhr) {
            console.log("error", xhr.responseText);
            showDangerToast('Gagal menambah soal');
        }
    });
}

function tambahSoalPg(e) {
    if ($(e).hasClass('disabled')) return;
    requestTambahSoal($(e).data('bank'), $(e).data('nomor'), 1, 1);
}

function tambahSoalPg2(e) {
    if ($(e).hasClass('disabled')) return;
    requestTambahSoal($(e).data('bank'), $(e).data('nomor'), 2, 2);
}

function tambahSoalJodohkan(e) {
    if ($(e).hasClass('disabled')) return;
    requestTambahSoal($(e).data('bank'), $(e).data('nomor'), 3, 3);
}

function tambahSoalIsian(e) {
    if ($(e).hasClass('disabled')) return;
    requestTambahSoal($(e).data('bank'), $(e).data('nomor'), 4, 4);
}

function tambahSoalEssai(e) {
    if ($(e).hasClass('disabled')) return;
    requestTambahSoal($(e).data('bank'), $(e).data('nomor'), 5, 5);
}

function uploadAttach(action, data) {
    $.ajax({
        type: "POST",
        enctype: 'multipart/form-data',
        url: action,
        data: data,
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        success: function (data) {
            console.log('result', data);
            if (data.status) {
                dataFiles = data.files;
                /*
                var item = {};
                item ['size'] = data.size;
                item ["type"] = data.type;
                item ["src"] = data.src;
                item ["name"] = data.filename;
                dataFiles.push(item);
                console.log(data.type);
                */
                createPreviewFile();
            } else {
                Swal.fire({
                    title: "ERROR",
                    html: data.src,
                    icon: "error",
                    showCancelButton: false
                });
            }
        },
        error: function (e) {
            console.log("error", e.responseText);
            showDangerToast("file tidak terbaca");
        }
    });
}

function createPreviewFile(/*elem, event*/) {
    //var files = event.target.files;
    for (var j = 0; j < dataFiles.length; j++) {
        let file = dataFiles[j];
        //names_files.push(elem.get(0).files[j].name);
        if (file.file_name !== "") {

            var div = document.createElement("li");
            div.setAttribute("id", "f-" + file.alias);
            if (!$("#f-" + file.alias).length) {
                if (file.type.match('image')) {
                    div.innerHTML = "<img src='" + base_url + file.src + "'/>" +
                        "<div  class='post-thumb'>" +
                        "<div class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.alias + "' class='remove-pic'>" +
                        "<i class='bi bi-x' aria-hidden='true'></i></a>" +
                        "<div>" +
                        "</div>";
                    $("#media-list").prepend(div);
                } else if (file.type.match('video')) {
                    div.innerHTML = "<video src='" + file.src + "'></video>" +
                        "<div class='post-thumb'>" +
                        "<div  class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.alias + "' class='remove-pic'>" +
                        "<i class='bi bi-x' aria-hidden='true'></i></a>" +
                        "<div>" +
                        "</div>";
                    $("#media-list").prepend(div);
                } else {
                    div.innerHTML = "<img src='" + base_url + "/assets/app/img/document_file.png'>" +
                        "<div class='post-thumb'>" +
                        "<div  class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.alias + "' class='remove-pic'>" +
                        "<i class='bi bi-x' aria-hidden='true'></i></a>" +
                        "<div>" +
                        "</div>";
                    $("#media-list").prepend(div);
                }
            }
        }
    }
    console.log(dataFiles);
}

$('body').on('click', '.remove-pic', function () {
    $(this).parent().parent().parent().remove();
    var removeItem = $(this).attr('data-id');

    for (var i = 0; i < dataFiles.length; i++) {
        var cur = dataFiles[i];
        if (cur.alias === removeItem) {
            dataFiles.splice(i, 1);
            deleteImage(cur.src);
            break;
        }
    }
    console.log(dataFiles);
});

function deleteImage(src) {
    $.ajax({
        data: { src: src },
        type: "POST",
        url: base_url + "cbtbanksoal/deletefile",
        cache: false,
        success: function (response) {
            console.log(response);
        }
    });
}

function HasArabicCharacters(text) {
    var arregex = /[\u0600-\u06FF]/;
    return arregex.test(text);
}

function deleteOpsiPg2(e) {
    $(e).closest('.pg-kompleks').remove();

    var opsi2 = $('#opsi-pg2');
    const $abjads = opsi2.find('.abjad-pg2')
    const $badges = opsi2.find('.pg2-letter-badge')

    $abjads.each(function (index, sp) {
        $(sp).html('<b>Jawaban ' + (index + 10).toString(36).toUpperCase() + '</b>')
    })
    $badges.each(function (index, badge) {
        $(badge).text((index + 10).toString(36).toUpperCase())
    })

    const $teks = opsi2.find('.textjawaban2');
    $teks.each(function (index, area) {
        const abc = (index + 10).toString(36).toLowerCase();
        $(area).prop('id', 'textjawaban2_' + abc).prop('name', 'jawaban2_' + abc)
    })

    const $checks = opsi2.find('.check-pg2');
    $checks.each(function (index) {
        const abc = (index + 10).toString(36).toLowerCase();
        $(this).val(abc);
    })

    const $bsSelects = opsi2.find('.pg2-bs-select');
    $bsSelects.each(function (index) {
        const abc = (index + 10).toString(36).toLowerCase();
        $(this).data('letter', abc).attr('data-letter', abc);
    })

    updatePg2CheckedCount();
}

function showEditDialog(el) {
    valSelected = $(el).closest('.float-right').prev();
    let dialog = $('#noteModal');
    let editorCode = $('#edit-dialog');
    dialog.modal('show')
    editorCode.summernote('code', valSelected.html())
}

function simpanValue() {
    let editorCode = $('#edit-dialog');
    valSelected.html(editorCode.summernote('code'));
    $('#noteModal').modal('hide');
}



function encode(str) {
    var decoded = decodeURIComponent(str)
    var isEncoded = decoded !== str
    var encoded = encodeURIComponent(str)
    if (isEncoded) {
        return str
    } else {
        return encoded
    }
}

function decode(str) {
    var decoded = decodeURIComponent(str)
    var encoded = encodeURIComponent(decoded)
    var isEncoded = encoded === str
    if (isEncoded) {
        return decoded
    } else {
        return str
    }
}


function tidySoal() {
    console.log('Starting tidySoal...');
    var content = $('#textsoal').summernote('code');
    // Basic check if content is effectively empty
    if (!content || content.trim() === '' || content === '<p><br></p>') {
        Swal.fire('Peringatan', 'Silahkan tulis atau paste soal terlebih dahulu sebelum menggunakan fitur Rapikan AI.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Sedang Memproses...',
        text: 'AI sedang merapikan struktur HTML, tabel, dan layout soal (beserta opsi jawaban)...',
        allowOutsideClick: false,
        onBeforeOpen: () => {
            Swal.showLoading()
        }
    });

    // Retrieve CSRF token from textsoal data attributes
    var csrfName = $('#textsoal').data('name');
    var csrfHash = $('#textsoal').data('id');

    var formData = new FormData();
    formData.append('soal', content);

    // Determine active tab/type just like in paraphraseSoal
    var urlParams = new URLSearchParams(window.location.search);
    var jenis = urlParams.get('jns');
    if (!jenis) jenis = '1';

    // Collect options
    var opsi = {};
    if (jenis === '1') {
        opsi['a'] = $('#textjawaban_a').summernote('code');
        opsi['b'] = $('#textjawaban_b').summernote('code');
        opsi['c'] = $('#textjawaban_c').summernote('code');
        opsi['d'] = $('#textjawaban_d').summernote('code');
        opsi['e'] = $('#textjawaban_e').summernote('code');
    } else if (jenis === '2') {
        $('.textjawaban2').each(function () {
            var name = $(this).attr('name');
            var key = name.split('_')[1];
            opsi[key] = $(this).val();
        });
    }

    formData.append('opsi', JSON.stringify(opsi));
    formData.append('jenis', jenis);
    if (csrfName) formData.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + "cbtbanksoal/tidy_ai",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "JSON",
        success: function (data) {
            if (data.success) {
                // Update Soal
                if (data.data) {
                    $('#textsoal').summernote('code', data.data);
                }

                // Update Options if available in response
                if (data.opsi) {
                    if (jenis === '1') {
                        if (data.opsi.a) $('#textjawaban_a').summernote('code', data.opsi.a);
                        if (data.opsi.b) $('#textjawaban_b').summernote('code', data.opsi.b);
                        if (data.opsi.c) $('#textjawaban_c').summernote('code', data.opsi.c);
                        if (data.opsi.d) $('#textjawaban_d').summernote('code', data.opsi.d);
                        if (data.opsi.e) $('#textjawaban_e').summernote('code', data.opsi.e);
                    } else if (jenis === '2') {
                        $.each(data.opsi, function (k, v) {
                            $('#textjawaban2_' + k).summernote('code', v);
                        });
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Selesai!',
                    text: 'Struktur soal dan jawaban berhasil dirapikan.',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'AI tidak dapat memproses permintaan.'
                });
            }
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal menghubungi server atau timeout.'
            });
        }
    });
}

function paraphraseSoal() {
    var content = $('#textsoal').summernote('code');
    // Basic check if content is empty (stripping tags)
    if (!content || $('<div>').html(content).text().trim() === '') {
        Swal.fire('Peringatan', 'Silahkan tulis narasi soal terlebih dahulu sebelum menggunakan fitur Parafrase.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Sedang Memproses...',
        text: 'AI sedang menganalisis dan meningkatkan kualitas soal (HOTS/Literasi)...',
        allowOutsideClick: false,
        onBeforeOpen: () => {
            Swal.showLoading()
        }
    });

    // Retrieve CSRF token from textsoal data attributes
    var csrfName = $('#textsoal').data('name');
    var csrfHash = $('#textsoal').data('id');

    var formData = new FormData();
    formData.append('soal', content);
    formData.append('bank_id', idBank);
    var urlParams = new URLSearchParams(window.location.search);
    var jenis = urlParams.get('jns');
    // If not found in URL (e.g. first load), default to '1' or active tab
    if (!jenis) {
        jenis = typeof tab_active !== 'undefined' ? String(tab_active) : '1';
    }

    // Collect options if available (PG or Complex)
    var opsi = {};
    if (jenis === '1') {
        ['a', 'b', 'c', 'd', 'e'].forEach(function(k) {
            var $el = $('#textjawaban_' + k);
            if ($el.length) {
                opsi[k] = ($el.next('.note-editor').length && typeof $el.summernote === 'function') ? $el.summernote('code') : $el.val();
            }
        });
    } else if (jenis === '2') {
        // For complex multiple choice, options are dynamic
        $('.textjawaban2').each(function () {
            var name = $(this).attr('name') || ''; // jawaban2_a
            var id = $(this).attr('id') || '';
            var key = id ? id.replace('textjawaban2_', '') : name.replace('jawaban2_', '');
            if (key) {
                opsi[key] = ($(this).next('.note-editor').length && typeof $(this).summernote === 'function') ? $(this).summernote('code') : $(this).val();
            }
        });
    }

    // Add options to formData
    formData.append('opsi', JSON.stringify(opsi));
    formData.append('jenis', jenis);
    if (csrfName) formData.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + "cbtbanksoal/paraphrase_ai",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "JSON",
        success: function (data) {
            if (data.success) {
                // Set question text
                if ($('#textsoal').next('.note-editor').length && typeof $('#textsoal').summernote === 'function') {
                    $('#textsoal').summernote('code', data.data);
                } else {
                    $('#textsoal').val(data.data);
                }

                // Update options if returned separately
                if (data.opsi && typeof data.opsi === 'object') {
                    if (jenis === '1') {
                        ['a', 'b', 'c', 'd', 'e'].forEach(function (k) {
                            if (data.opsi[k]) {
                                var $el = $('#textjawaban_' + k);
                                if ($el.length) {
                                    if ($el.next('.note-editor').length && typeof $el.summernote === 'function') {
                                        $el.summernote('code', data.opsi[k]);
                                    } else {
                                        $el.val(data.opsi[k]);
                                    }
                                }
                            }
                        });
                    } else if (jenis === '2') {
                        Object.keys(data.opsi).forEach(function (k) {
                            var $el = $('#textjawaban2_' + k);
                            if ($el.length) {
                                if ($el.next('.note-editor').length && typeof $el.summernote === 'function') {
                                    $el.summernote('code', data.opsi[k]);
                                } else {
                                    $el.val(data.opsi[k]);
                                }
                            }
                        });
                    }
                }

                if (typeof updateCharCount === 'function') {
                    updateCharCount();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Selesai!',
                    text: 'Soal berhasil diperbaiki menjadi HOTS & Kontekstual.',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'AI tidak dapat memproses permintaan.'
                });
            }
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal menghubungi server atau timeout.'
            });
        }
    });
}




function tidyManual() {
    console.log('Starting tidyManual...');
    var content = $('#textsoal').summernote('code');
    // Basic check if content is effectively empty
    if (!content || content.trim() === '' || content === '<p><br></p>') {
        Swal.fire('Peringatan', 'Silahkan tulis atau paste soal terlebih dahulu sebelum menggunakan fitur Rapikan Manual.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Sedang Memproses...',
        text: 'Sedang merapikan struktur HTML, tabel, dan layout soal (Manual)...',
        allowOutsideClick: false,
        onBeforeOpen: () => {
            Swal.showLoading()
        }
    });

    // Retrieve CSRF token from textsoal data attributes
    var csrfName = $('#textsoal').data('name');
    var csrfHash = $('#textsoal').data('id');

    var formData = new FormData();
    formData.append('soal', content);

    // Determine active tab/type just like in paraphraseSoal
    var urlParams = new URLSearchParams(window.location.search);
    var jenis = urlParams.get('jns');
    if (!jenis) jenis = '1';

    // Collect options
    var opsi = {};
    if (jenis === '1') {
        opsi['a'] = $('#textjawaban_a').summernote('code');
        opsi['b'] = $('#textjawaban_b').summernote('code');
        opsi['c'] = $('#textjawaban_c').summernote('code');
        opsi['d'] = $('#textjawaban_d').summernote('code');
        opsi['e'] = $('#textjawaban_e').summernote('code');
    } else if (jenis === '2') {
        $('.textjawaban2').each(function () {
            var name = $(this).attr('name');
            var key = name.split('_')[1];
            opsi[key] = $(this).val();
        });
    }

    formData.append('opsi', JSON.stringify(opsi));
    if (csrfName) formData.append(csrfName, csrfHash);

    $.ajax({
        url: base_url + "cbtbanksoal/tidy_manual",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "JSON",
        success: function (data) {
            if (data.success) {
                // Update Soal
                if (data.data) {
                    $('#textsoal').summernote('code', data.data);
                }

                // Update Options if available in response
                if (data.opsi) {
                    if (jenis === '1') {
                        if (data.opsi.a) $('#textjawaban_a').summernote('code', data.opsi.a);
                        if (data.opsi.b) $('#textjawaban_b').summernote('code', data.opsi.b);
                        if (data.opsi.c) $('#textjawaban_c').summernote('code', data.opsi.c);
                        if (data.opsi.d) $('#textjawaban_d').summernote('code', data.opsi.d);
                        if (data.opsi.e) $('#textjawaban_e').summernote('code', data.opsi.e);
                    } else if (jenis === '2') {
                        $.each(data.opsi, function (k, v) {
                            $('#textjawaban2_' + k).summernote('code', v);
                        });
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Selesai!',
                    text: 'Struktur soal dan jawaban berhasil dirapikan (Manual).',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Gagal memproses permintaan.'
                });
            }
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal menghubungi server atau timeout.'
            });
        }
    });
}

console.log("soal.js loaded completely");

// =====================================================
// TYPE 6: URUTAN (ORDERING) - Teacher Functions
// =====================================================
var urutanItemCount = 2;

function tambahSoalUrutan(el) {
    if ($(el).hasClass('disabled')) return;
    requestTambahSoal($(el).data('bank'), $(el).data('nomor'), 6, 6);
}

// Helper to build Urutan card HTML
function buildUrutanCard(index, placeholder, value = '') {
    var num = index + 1;
    return '<div class="urutan-item-card" data-idx="' + index + '">' +
        '<i class="bi bi-grip-vertical urutan-handle"></i>' +
        '<div class="urutan-num-badge">' + num + '</div>' +
        '<input type="text" class="form-control urutan-input flex-grow-1" placeholder="' + placeholder + '" value="' + value + '">' +
        '<button type="button" class="urutan-remove btn-remove-urutan" onclick="removeUrutanItem(this)" title="Hapus item">' +
        '<i class="bi bi-trash"></i>' +
        '</button>' +
        '</div>';
}

function resetUrutanItems() {
    urutanItemCount = 2;
    var html = buildUrutanCard(0, 'Item pertama (urutan benar)') +
        buildUrutanCard(1, 'Item kedua');
    $('#urutan-items-container').html(html);
}

function addUrutanItem() {
    urutanItemCount++;
    $('#urutan-items-container').append(buildUrutanCard(urutanItemCount - 1, 'Item ke-' + urutanItemCount));
    updateUrutanNumbers();
}

function removeUrutanItem(btn) {
    if ($('.urutan-item-card').length > 2) {
        $(btn).closest('.urutan-item-card').remove();
        updateUrutanNumbers();
    } else {
        Swal.fire('Info', 'Minimal 2 item diperlukan', 'info');
    }
}

function updateUrutanNumbers() {
    $('.urutan-item-card').each(function (idx) {
        $(this).find('.urutan-num-badge').text((idx + 1));
        $(this).attr('data-idx', idx);
        $(this).find('.urutan-input').attr('placeholder', 'Item ke-' + (idx + 1));
    });
    urutanItemCount = $('.urutan-item-card').length;
}

function getUrutanData() {
    var items = [];
    $('.urutan-input').each(function () {
        var val = $(this).val().trim();
        if (val) items.push(val);
    });
    return { items: items, correct_order: items.map(function (_, i) { return i; }) };
}

// Event listener for adding urutan items
$(document).on('click', '#tambah-item-urutan', function () {
    addUrutanItem();
});

// =====================================================
// TYPE 7: ISIAN RUMPANG (CLOZE) - Teacher Functions
// =====================================================
function tambahSoalCloze(el) {
    if ($(el).hasClass('disabled')) return;
    requestTambahSoal($(el).data('bank'), $(el).data('nomor'), 7, 7);
}

function parseClozeBlankIds(text) {
    // Strip HTML tags to find "clean" keys, but keep original for text replacement? 
    // Actually regex on HTML content for [[...]] works fine as long as tags aren't INSIDE the brackets like [[<b>word</b>]].
    // If they are, we strip them for the ID.
    var regex = /\[\[([^\]]+)\]\]/g;
    var matches = [];
    var match;
    while ((match = regex.exec(text)) !== null) {
        var cleanKey = match[1].replace(/<[^>]*>?/gm, ''); // Remove any HTML tags inside the key
        if (matches.indexOf(cleanKey) === -1 && cleanKey.trim() !== '') {
            matches.push(cleanKey);
        }
    }
    return matches;
}

// Color palette for blank highlights
var clozeColors = [
    { bg: '#dbeafe', border: '#3b82f6', text: '#1e40af', badge: '#3b82f6' },
    { bg: '#dcfce7', border: '#22c55e', text: '#166534', badge: '#22c55e' },
    { bg: '#fef3c7', border: '#f59e0b', text: '#92400e', badge: '#f59e0b' },
    { bg: '#fce7f3', border: '#ec4899', text: '#9d174d', badge: '#ec4899' },
    { bg: '#e0e7ff', border: '#6366f1', text: '#3730a3', badge: '#6366f1' },
    { bg: '#ccfbf1', border: '#14b8a6', text: '#115e59', badge: '#14b8a6' },
    { bg: '#fee2e2', border: '#ef4444', text: '#991b1b', badge: '#ef4444' },
    { bg: '#f3e8ff', border: '#a855f7', text: '#6b21a8', badge: '#a855f7' },
];

function getClozeColor(index) {
    return clozeColors[index % clozeColors.length];
}

function updateClozePreview() {
    var htmlContent = $('#textsoal').summernote('code');
    var tempDiv = document.createElement("div");
    tempDiv.innerHTML = htmlContent;
    var text = tempDiv.textContent || tempDiv.innerText || ""; // Get plain text for parsing logic, or use HTML regex if needed.
    // However, for cloze matching in Summernote we might need to parse the HTML carefully or just use the text.
    // Let's stick to parsing text content for now but we need to identify them in the HTML for preview. DANGER: formatting might break blank detection if [[ is bold and ]] is not.
    // BETTER APPROACH: Parse the HTML content directly for [[...]]

    // Simple approach: Extract text, find blanks, render preview. 
    // Ideally we want to see the formatting in preview too, but replacing [[..]] with badges.

    var preview = $('#cloze-live-preview');

    if (!text || text.trim() === '') {
        preview.html('<span class="text-muted fst-italic small">Preview akan muncul saat Anda mengetik teks di editor soal...</span>');
        return;
    }

    var blankIds = parseClozeBlankIds(htmlContent); // We parse from HTML to support formatting
    var colorMap = {};
    blankIds.forEach(function (id, idx) {
        colorMap[id] = getClozeColor(idx);
    });

    // Replace [[...]] in HTML with styled blank placeholders
    // We use a regex that is tolerant of HTML tags inside if possible, but standard [[word]] usually won't have tags inside unless user bolds half of it.
    // Let's assume user bolds the whole thing: <b>[[word]]</b>.

    var html = htmlContent.replace(/\[\[([^\]]+)\]\]/g, function (match, blankId) {
        // Strip HTML tags from blankId just in case
        var cleanId = blankId.replace(/<[^>]*>?/gm, '');
        var color = colorMap[cleanId] || getClozeColor(0);
        return '<span class="cloze-preview-blank" style="' +
            'display:inline-block;min-width:80px;padding:2px 12px;margin:0 2px;' +
            'border-bottom:3px solid ' + color.border + ';' +
            'background:' + color.bg + ';' +
            'color:' + color.text + ';' +
            'border-radius:4px;font-weight:600;text-align:center;font-size:13px;' +
            'transition:all .2s;' +
            '">' +
            '<i class="bi bi-three-dots" style="font-size:11px;opacity:.5;"></i> ' +
            '</span>';
    });

    // Output HTML directly
    preview.html(html);
}

function updateClozeAnswers() {
    var text = $('#textsoal').summernote('code');
    var blankIds = parseClozeBlankIds(text);
    var container = $('#cloze-answers-container');

    // Update blank counter
    var countEl = $('#cloze-blank-count');
    if (blankIds.length > 0) {
        countEl.html('<i class="bi bi-hash"></i> ' + blankIds.length + ' rumpang terdeteksi');
    } else {
        countEl.html('0 Rumpang Terdeteksi');
    }
    // Preserve existing values
    var existingValues = {};
    container.find('.cloze-answer-input').each(function () {
        existingValues[$(this).data('blank')] = $(this).val();
    });

    container.html('');

    if (blankIds.length === 0) {
        container.html(
            '<div class="alert alert-light border small text-center py-3" style="border-radius:10px;">' +
            '<i class="bi bi-input-cursor-text d-block mb-1" style="font-size:1.5rem;color:#adb5bd;"></i>' +
            'Ketik teks di editor kiri menggunakan format <code>[[kata]]</code> untuk membuat bagian rumpang.' +
            '</div>'
        );
        updateClozePreview();
        return;
    }

    blankIds.forEach(function (blankId, index) {
        var existingVal = existingValues[blankId] !== undefined ? existingValues[blankId] : blankId;
        var color = getClozeColor(index);
        container.append(
            '<div class="d-flex align-items-center gap-2 mb-2 cloze-answer-card" style="' +
            'padding:10px 14px;border-radius:10px;border:1px solid ' + color.border + '33;' +
            'background:' + color.bg + '55;transition:all .2s;">' +
            '<span class="badge rounded-pill" style="background:' + color.badge + ';min-width:28px;font-size:12px;">' + (index + 1) + '</span>' +
            '<input type="text" class="form-control form-control-sm cloze-answer-input" data-blank="' + blankId + '" ' +
            'value="' + $('<span>').text(existingVal).html() + '" ' +
            'placeholder="Ketik jawaban benar..." ' +
            'style="border-radius:8px;border:1px solid ' + color.border + '66;font-size:13px;">' +
            '</div>'
        );
    });

    // Update preview too
    updateClozePreview();
}



function getClozeData() {
    var blanks = {};
    $('.cloze-answer-input').each(function () {
        var blankId = $(this).data('blank');
        var answer = $(this).val().trim();
        if (blankId && answer) {
            blanks[blankId] = { answer: answer, case_sensitive: false };
        }
    });
    return { blanks: blanks };
}

// Insert blank at cursor position
// Insert blank at cursor position in Summernote
$(document).on('click', '#btn-insert-blank', function () {
    var text = $('#textsoal').summernote('code');
    var blankIds = parseClozeBlankIds(text);

    // Find next available number
    var nextNum = 1;
    while (blankIds.indexOf(String(nextNum)) !== -1) {
        nextNum++;
    }

    // Style for the blank
    var span = $('<span class="cloze-tag" style="background-color:#fff3cd; padding:2px 6px; border-radius:4px; border:1px solid #ffe69c; color:#664d03; font-weight:bold;">[[' + nextNum + ']]</span>')[0];

    // Insert HTML node at Summernote cursor
    $('#textsoal').summernote('editor.restoreRange');
    $('#textsoal').summernote('editor.focus');
    $('#textsoal').summernote('insertNode', span);
    // Add space after to prevent typing inside the span
    $('#textsoal').summernote('editor.insertText', ' ');

    // Trigger update
    updateClozeAnswers();
});

// Event listeners for cloze
$(document).on('click', '#parse-cloze-blanks', function () {
    updateClozeAnswers();
});

// Bind Summernote change event to update Cloze
// This needs to be added to the Summernote callbacks in init, but we can also use a generic interval or bind to the note-editable
$(document).on('summernote.change', '#textsoal', function (we, contents, $editable) {
    if ($('#jenis-id').val() == '7') { // Only if Cloze type is active
        clearTimeout(window.clozeParseTimeout);
        window.clozeParseTimeout = setTimeout(function () {
            updateClozeAnswers();
        }, 500);
    }
});

// Also bind to keyup on the editor area just in case
$(document).on('keyup', '.note-editable', function () {
    if ($('#jenis-id').val() == '7') {
        clearTimeout(window.clozeParseTimeout);
        window.clozeParseTimeout = setTimeout(function () {
            updateClozeAnswers();
        }, 800);
    }
});