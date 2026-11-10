var table;
$(document).ready(function () {
    ajaxcsrf();

    $('.select2').select2({
        theme: "bootstrap4",
    });

    table = $("#jurusan").DataTable(
        {
            order: [],
            "aoColumnDefs": [
                { "bSortable": false, "aTargets": [0, 1, 5] },
                //{ "bSearchable": false, "aTargets": [ 0, 1, 2, 3 ] }
            ],
            dom:
                "<'row'<'toolbar col-sm-6'lfrtip><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            /*
            columnDefs: [ {
                "targets": 0,
                "orderable": false
            } ]
             */
        }
    );
    table
        .buttons()
        .container()
        .appendTo("#jurusan_wrapper .col-md-6:eq(1)");

    $("div.toolbar").html(
        '<button id="hapusterpilih" onclick="bulk_delete()" type="button" class="btn btn-danger mr-3 d-none" data-toggle="tooltip" title="Hapus Terpilh"><i class="b bi-trash"></i></button>'
    );

    $("#select_all").on("click", function () {
        if (this.checked) {
            $(".check").each(function () {
                this.checked = true;
                $("#select_all").prop("checked", true);
                $('#hapusterpilih').removeClass('d-none');
            });
        } else {
            $(".check").each(function () {
                this.checked = false;
                $("#select_all").prop("checked", false);
                $('#hapusterpilih').addClass('d-none');
            });
        }
    });

    $("#jurusan tbody").on("click", "tr .check", function () {
        var check = $("#jurusan tbody tr .check").length;
        var checked = $("#jurusan tbody tr .check:checked").length;
        if (check === checked) {
            $("#select_all").prop("checked", true);
        } else {
            $("#select_all").prop("checked", false);
        }
        if (checked === 0) {
            $('#hapusterpilih').addClass('d-none');
        } else {
            $('#hapusterpilih').removeClass('d-none');
        }
    });

    /*
    $('#createJurusanModal').on('show.bs.modal', function (e) {
        var nama = $(e.relatedTarget).data('nama');
        var kode = $(e.relatedTarget).data('kode');
        var mapel = $(e.relatedTarget).data('mapel');

        var arrSel = [];
        if (mapel != null) {
            arrSel = mapel.split(',');
        }
        console.log(arrSel);

        $("#createnama").val(nama);
        $("#createkode").val(kode);

        var selMapel = $("#create_mapel_peminatan");
        selMapel.html('');
        for (var key in mapels) {
            if (mapels.hasOwnProperty(key)) {
                var selected = jQuery.inArray(key, arrSel) > -1;
                selMapel.append(new Option(mapels[key], key, false, selected));
            }
        }
    });
     */

    $('#editJurusanModal').on('show.bs.modal', function (e) {
        var id = $(e.relatedTarget).data('id');
        var nama = $(e.relatedTarget).data('nama');
        var kode = $(e.relatedTarget).data('kode');
        var mapel = $(e.relatedTarget).data('mapel');

        var arrSel = [];
        if (mapel != null) {
            arrSel = String(mapel).split(',').map(item => item.trim());
        }

        console.log('Mapels Data:', mapels);
        console.log('Selected Keys:', arrSel);

        $("#namaEdit").val(nama);
        $("#kodeEdit").val(kode);
        $("#editIdJurusan").val(id);

        // Filter out empty strings and whitespace
        var cleanSel = arrSel.filter(function (el) {
            return el != null && el.trim() != "";
        });

        console.log('Selected Keys (Clean):', cleanSel);

        // Populate and select
        // Iterate over the global mapels object (e.g. {C: {...}, LM: {...}})
        for (var kodeGroup in mapels) {
            if (mapels.hasOwnProperty(kodeGroup)) {
                // Find the specific select for this group
                var selMapel = $('#mapel_peminatan' + kodeGroup);

                if (selMapel.length > 0) {
                    // 1. Destroy existing Select2 to handle DOM manipulation safely
                    if (selMapel.hasClass("select2-hidden-accessible")) {
                        selMapel.select2('destroy');
                    }

                    // 2. Clear and Repopulate Options
                    selMapel.empty();
                    var groupOptions = mapels[kodeGroup];
                    for (var mapelId in groupOptions) {
                        if (groupOptions.hasOwnProperty(mapelId)) {
                            var optionText = groupOptions[mapelId];
                            var newOption = new Option(optionText, mapelId, false, false);
                            selMapel.append(newOption);
                        }
                    }

                    // Helper to generate consistent random pastel colors based on text
                    var getColor = function (text) {
                        var colors = [
                            { border: '#ffa39e', bg: '#fff1f0', text: '#cf1322' }, // Red
                            { border: '#ffbb96', bg: '#fff7e6', text: '#d4380d' }, // Orange
                            { border: '#ffe58f', bg: '#fffbe6', text: '#d48806' }, // Gold
                            { border: '#b7eb8f', bg: '#f6ffed', text: '#389e0d' }, // Green
                            { border: '#87e8de', bg: '#e6fffb', text: '#08979c' }, // Cyan
                            { border: '#91d5ff', bg: '#e6f7ff', text: '#096dd9' }, // Blue
                            { border: '#adc6ff', bg: '#f0f5ff', text: '#1d39c4' }, // Geek Blue
                            { border: '#d3adf7', bg: '#f9f0ff', text: '#531dab' }, // Purple
                            { border: '#ffadd2', bg: '#fff0f6', text: '#c41d7f' }  // Magenta
                        ];
                        var hash = 0;
                        for (var i = 0; i < text.length; i++) {
                            hash = text.charCodeAt(i) + ((hash << 5) - hash);
                        }
                        return colors[Math.abs(hash) % colors.length];
                    };

                    // 3. Re-initialize Select2 with dropdownParent and custom templates
                    selMapel.select2({
                        theme: "bootstrap4",
                        width: '100%',
                        placeholder: "Pilih Mapel",
                        dropdownParent: $('#editJurusanModal'),
                        templateResult: function (data) {
                            if (!data.id) {
                                return data.text;
                            }
                            var color = getColor(data.text);
                            var $result = $('<span>' + data.text + '</span>');
                            if (data.selected) {
                                $result.css({
                                    'background-color': '#ffffff', // Keep background white
                                    'color': color.text,
                                    'border': '1px solid ' + color.border,
                                    'border-radius': '4px',
                                    'padding': '4px 10px',
                                    'margin': '2px 0',
                                    'display': 'block'
                                });
                                $result.addClass('font-weight-bold');
                                $result.append(' <i class="bi bi-check ml-1 float-right mt-1"></i>');
                            } else {
                                $result.css('padding', '4px 10px');
                            }
                            return $result;
                        },
                        templateSelection: function (data) {
                            if (!data.id) {
                                return data.text;
                            }
                            var color = getColor(data.text);
                            // Container acts as the tag
                            var $selection = $('<span></span>');
                            $selection.css({
                                'background-color': color.bg,
                                'color': color.text,
                                'border': '1px solid ' + color.border,
                                'border-radius': '4px',
                                'padding': '1px 6px',
                                'font-weight': '500',
                                'display': 'inline-flex',
                                'align-items': 'center'
                            });

                            // Custom remove icon inside the tag
                            var $remove = $('<span class="select2-selection__choice__remove" style="cursor:pointer; margin-right:6px; font-weight:bold; color:' + color.text + '">×</span>');

                            $selection.append($remove);
                            $selection.append(data.text);

                            return $selection;
                        }
                    });

                    // Inject CSS to ensure horizontal layout (flex) and proper spacing for choices
                    if (!$('#select2-custom-style').length) {
                        $('<style id="select2-custom-style">' +
                            '.select2-selection__rendered { display: flex !important; flex-wrap: wrap !important; list-style: none !important; padding-left: 0 !important; }' +
                            '.select2-selection__choice { display: flex !important; align-items: center !important; border: none !important; background: transparent !important; margin: 2px 4px 2px 0 !important; }' +
                            /* Hide default remove button (direct child) */
                            '.select2-selection__choice > .select2-selection__choice__remove { display: none !important; }' +
                            '.select2-search--inline { float: none !important; }' +
                            '.select2-search__field { margin-top: 5px !important; }' +
                            /* Custom Hover Color - Clean White */
                            '.select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #ffffff !important; color: #000000 !important; }' +
                            /* Force Dropdown Background White */
                            '.select2-dropdown { background-color: #ffffff !important; border: 1px solid #ced4da; }' +
                            '.select2-results__options { background-color: #ffffff !important; }' +
                            '.select2-results__option { background-color: #ffffff !important; margin: 0 !important; }' +
                            /* Remove the dot artifact */
                            '.select2-container .select2-selection--multiple .select2-selection__rendered::after { content: none !important; display: none !important; }' +
                            '</style>').appendTo('head');
                    }

                    // 4. Apply selection specifically for this dropdown
                    // We calculate the intersection of all selected IDs and the filtered options in this specific select
                    var valuesForThisSelect = [];
                    selMapel.find('option').each(function () {
                        if (cleanSel.includes($(this).val())) {
                            valuesForThisSelect.push($(this).val());
                        }
                    });

                    selMapel.val(valuesForThisSelect).trigger('change');
                }
            }
        }
    });

    $('#create').submit('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        //var nama = $('#createnama').val();
        //var kode = $('#createkode').val();
        console.log("data:", $(this).serialize());

        $.ajax({
            url: base_url + "datajurusan/add",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function (data) {
                console.log("result", data);
                $('#createJurusanModal').modal('hide').data('bs.modal', null);
                $('#createJurusanModal').on('hidden', function () {
                    $(this).data('modal', null);  // destroys modal
                });
                //showSuccessToast('Data berhasil disimpan.');
                //table.ajax.reload();
                window.location.reload(true);
            }, error: function (xhr, status, error) {
                $('#createJurusanModal').modal('hide').data('bs.modal', null);
                $('#createJurusanModal').on('hidden', function () {
                    $(this).data('modal', null);  // destroys modal
                });
                console.log("error", xhr.responseText);
                showDangerToast('Error');
            }
        });
    });

    $('#update').on('submit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var btn = $('#submit');
        btn.attr('disabled', 'disabled').text('Wait...');

        console.log("data", $(this).serialize());

        $.ajax({
            url: base_url + "datajurusan/update",
            data: $(this).serialize(),
            method: 'POST',
            dataType: "JSON",
            success: function (data) {
                console.log("result", data);
                btn.removeAttr('disabled').text('Simpan');
                $('#editJurusanModal').modal('hide').data('bs.modal', null);
                $('#editJurusanModal').on('hidden', function () {
                    $(this).data('modal', null);  // destroys modal
                });
                window.location.reload(true);
            },
            error: function (xhr, status, error) {
                $('#editJurusanModal').modal('hide').data('bs.modal', null);
                $('#editJurusanModal').on('hidden', function () {
                    $(this).data('modal', null);  // destroys modal
                });
                showDangerToast('Error');
            }
        });
    });

    $("#bulk").on("submit", function (e) {
        if ($(this).attr("action") == base_url + "datajurusan/delete") {
            e.preventDefault();
            e.stopImmediatePropagation();

            console.log("selected", $(this).serialize());
            $.ajax({
                url: $(this).attr("action"),
                data: $(this).serialize(),
                type: "POST",
                success: function (respon) {
                    console.log('jurusan', respon);
                    if (respon.status) {
                        //showSuccessToast(respon.total + " data berhasil dihapus");
                        window.location.reload(true);
                        //reload_ajax();
                    } else {
                        Swal.fire({
                            title: "Gagal",
                            html: respon.total,
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: "Gagal",
                        text: "Ada data yang sedang digunakan",
                        icon: "error"
                    });
                }
            });
        }
    });
});

function dismissEdit() {
    var count = $('#jurusan tr').length;
    console.log("size", "-->" + count);
    for (var i = 0; i < count; i++) {
        var inputs = document.getElementById('check' + i);
        if (inputs != null) {
            inputs.checked = false;
            console.log("id", "-->" + 'check' + i);
        }
    }
}

function deleteItem(id) {
    dismissEdit();
    var checkBox = document.getElementById("check" + id);
    checkBox.checked = true;
    bulk_delete(true, "check" + id);
}

function bulk_delete(frombody, id) {
    if ($("#jurusan tbody tr .check:checked").length == 0) {
        Swal.fire({
            title: "Failed",
            text: "Tidak ada data yang dipilih",
            icon: "error"
        });
    } else {
        $("#bulk").attr("action", base_url + "datajurusan/delete");
        Swal.fire({
            title: "Anda yakin?",
            text: "Data akan dihapus!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
        }).then(result => {
            if (result.value) {
                $("#bulk").submit();
            } else {
                if (frombody) {
                    var inputs = document.getElementById(id);
                    inputs.checked = false;
                }
            }
        });
    }
}
