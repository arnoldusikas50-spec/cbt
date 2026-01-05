<?php
/**

 * Date: 14/07/20
 * Time: 17:46
 */

$kelasSelected = json_encode($bank->bank_kelas ?? '[]');
?>

<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6">
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-end">
                        <i class="bi bi-arrow-left-circle"></i><span
                                class="d-none d-sm-inline-block ms-1">Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?= form_open('create', array('id' => 'create')) ?>
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6><?= $subjudul . ' - ' . $bank->bank_kode ?></h6>
                    </div>
                    <div class="card-tools ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-floppy me-1"></i>Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        echo form_hidden('old_kode', $bank->bank_kode);
                        echo form_hidden('id_bank', $bank->id_bank); ?>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label>Kode Bank Soal</label>
                                <input type="text" class="form-control form-control-sm" name="kode" maxlength="20"
                                       placeholder="Masukan Kode Bank Soal" value="<?= $bank->bank_kode ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Mata Pelajaran</label>
                                <?php echo form_dropdown('mapel', $mapel, $bank->bank_mapel_id, 'id="select-mapel" class="select2 form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>Guru Pengampu</label>
                                <?php echo form_dropdown('guru', $gurus, $bank->bank_guru_id, 'id="select-guru" class="select2 form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-1 col-3">
                            <div class="mb-3">
                                <label>Level</label><br>
                                <?php
                                if ($setting->jenjang == "1") {
                                    for ($i = 1; $i < 7; $i++) {
                                        $arrLevel[$i] = $i;
                                    }
                                } else if ($setting->jenjang == "2") {
                                    for ($j = 7; $j < 10; $j++) {
                                        $arrLevel[$j] = $j;
                                    }
                                } else {
                                    for ($k = 10; $k < 13; $k++) {
                                        $arrLevel[$k] = $k;
                                    }
                                }

                                echo form_dropdown('level', $arrLevel, $bank->bank_level, 'id="select-level" class="form-control form-control-sm" data-placeholder="Pilih Level Kelas" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-9">
                            <div class="mb-3">
                                <label>Pilih Kelas</label><br>
                                <select name="kelas[]" id="select-kelas"
                                        class="select2 form-control form-control-sm" multiple="multiple"
                                        required=""></select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-7">
                            <div class="card" style="background-color: #cce5ff; color: #004085">
                                <div class="card-body p-2">
                                    <span><b>Soal Pilihan Ganda</b></span>
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="mb-3">
                                                <label>Jml. Soal</label>
                                                <input id="jml-pg" type='number' name='tampil_pg'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_pg ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="mb-3">
                                                <label>Bobot %</label>
                                                <input id="bobot-pg" type='number' name='bobot_pg'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_pg ?>"
                                                       required/>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="mb-3">
                                                <label>Opsi Jawaban</label>
                                                <?php
                                                $opsis [''] = 'Pilih Jml Opsi :';
                                                $opsis ['3'] = '3 (A, B, C)';
                                                $opsis ['4'] = '4 (A, B, C, D)';
                                                $opsis ['5'] = '5 (A, B, C, D, E)';

                                                echo form_dropdown('opsi', $opsis, $bank->opsi, 'class="form-control form-control-sm" required'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-5">
                            <div class="card" style="background-color: #fff3cd; color: #856404">
                                <div class="card-body p-2">
                                    <span><b>Soal Ganda Kompleks</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Jml. Soal</label>
                                                <input id="jml-pg2" type='number' name='tampil_kompleks'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_kompleks ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Bobot %</label>
                                                <input id="bobot-pg2" type='number' name='bobot_kompleks'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_kompleks ?>"
                                                       required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="card" style="background-color: #f8d7da; color: #721c24">
                                <div class="card-body p-2">
                                    <span><b>Soal Menjodohkan</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Jml. Soal</label>
                                                <input id="jml-jodohkan" type='number' name='tampil_jodohkan'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_jodohkan ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Bobot %</label>
                                                <input id="bobot-jodohkan" type='number' name='bobot_jodohkan'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_jodohkan ?>" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card" style="background-color: #d4edda; color: #155724">
                                <div class="card-body p-2">
                                    <span><b>Soal Isian Singkat</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Jml. Soal</label>
                                                <input id="jml-isian" type='number' name='tampil_isian'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_isian ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Bobot %</label>
                                                <input id="bobot-isian" type='number' name='bobot_isian'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_isian ?>" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card" style="background-color: #e2e3e5; color: #383d41">
                                <div class="card-body p-2">
                                    <span><b>Soal Uraian/Essai</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Jml. Soal</label>
                                                <input id="jml-essai" type='number' name='tampil_esai'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_esai ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Bobot %</label>
                                                <input id="bobot-essai" type='number' name='bobot_esai'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_esai ?>" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="card" style="background-color: #e0cffc; color: #5a379e">
                                <div class="card-body p-2">
                                    <span><b>Soal Urutan</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Jml. Soal</label>
                                                <input id="jml-urutan" type='number' name='tampil_urutan'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_urutan ?? 0 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Bobot %</label>
                                                <input id="bobot-urutan" type='number' name='bobot_urutan'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_urutan ?? 0 ?>" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card" style="background-color: #cfe2ff; color: #084298">
                                <div class="card-body p-2">
                                    <span><b>Soal Isian Rumpang</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Jml. Soal</label>
                                                <input id="jml-cloze" type='number' name='tampil_cloze'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_cloze ?? 0 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label>Bobot %</label>
                                                <input id="bobot-cloze" type='number' name='bobot_cloze'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_cloze ?? 0 ?>" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <table class="w-100">
                                        <tr class="text-center">
                                            <th>Total Soal</th>
                                            <th>Total Bobot</th>
                                        </tr>
                                        <tr class="text-center text-bold text-lg">
                                            <td id="total-soal">0</td>
                                            <td id="total-bobot">0</td>
                                        </tr>
                                    </table>
                                    <small></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="mb-3">
                                <label>Mapel Agama</label>
                                <?php
                                echo form_dropdown('soal_agama', $mapel_agama, $bank->soal_agama, 'class="form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="mb-3">
                                <label>Status Bank Soal</label>
                                <?php
                                $aktifs [''] = 'Pilih Status :';
                                $aktifs ['1'] = 'Aktif';
                                $aktifs ['0'] = 'Non Aktif';

                                echo form_dropdown('status', $aktifs, $bank->status, 'class="form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
</div>

<script>
    var isAdmin = '<?= $is_admin ?>';
    let kelasSelect = JSON.parse('<?= $kelasSelected ?>');
    var idGuru = '<?=$id_guru?>';
    var idMapel = '<?=$bank->bank_mapel_id?>';

    $(document).ready(function () {
        ajaxcsrf();
    var selLevel = $('#select-level');
        var selKelas = $('#select-kelas');
        var selMapel = $('#select-mapel');
        var selGuru = $('#select-guru');

        // Helper to generate consistent random pastel colors based on text
        var getColor = function(text) {
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

        // Inject CSS for custom Select2 styling
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
                '.select2-container .select2-selection--multiple .select2-selection__rendered::after { content: none !important; display: none !important; }' +
                '</style>').appendTo('head');
        }

        selMapel.select2({width: '100%', });
        selGuru.select2({width: '100%', });

        // Initialize selKelas with custom templates
        selKelas.select2({
            width: '100%',
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
                var $remove = $('<span class="select2-selection__choice__remove" style="cursor:pointer; margin-right:6px; font-weight:bold; color:'+color.text+'">×</span>');
                
                $selection.append($remove);
                $selection.append(data.text);
                
                return $selection;
            }
        });

        var as = [];
        for (let i = 0; i < kelasSelect.length; i++) {
            as.push(kelasSelect[i].kelas_id+'');
        }
        console.log('selct', as)
        getGuruMapel(selMapel.val());
        getKelasLevel(selLevel.val(), selMapel.val());

        $('#create').submit('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const bpg = $('#bobot-pg').val() === "" ? 0 : parseInt($('#bobot-pg').val());
            const bpg2 = $('#bobot-pg2').val() === "" ? 0 : parseInt($('#bobot-pg2').val());
            const bjod = $('#bobot-jodohkan').val() === "" ? 0 : parseInt($('#bobot-jodohkan').val());
            const bisian = $('#bobot-isian').val() === "" ? 0 : parseInt($('#bobot-isian').val());
            const besai = $('#bobot-essai').val() === "" ? 0 : parseInt($('#bobot-essai').val());
            const burutan = $('#bobot-urutan').val() === "" ? 0 : parseInt($('#bobot-urutan').val());
            const bcloze = $('#bobot-cloze').val() === "" ? 0 : parseInt($('#bobot-cloze').val());
            
            const jpg = $('#jml-pg').val() === "" ? 0 : parseInt($('#jml-pg').val());
            const jpg2 = $('#jml-pg2').val() === "" ? 0 : parseInt($('#jml-pg2').val());
            const jjod = $('#jml-jodohkan').val() === "" ? 0 : parseInt($('#jml-jodohkan').val());
            const jisian = $('#jml-isian').val() === "" ? 0 : parseInt($('#jml-isian').val());
            const jessai = $('#jml-essai').val() === "" ? 0 : parseInt($('#jml-essai').val());
            const jurutan = $('#jml-urutan').val() === "" ? 0 : parseInt($('#jml-urutan').val());
            const jcloze = $('#jml-cloze').val() === "" ? 0 : parseInt($('#jml-cloze').val());

            const total = bpg + bpg2 + bjod + bisian + besai + burutan + bcloze;

            if (total > 100) {
                Swal.fire({
                    title: "Perhatian",
                    text: "Total Bobot Soal melebihi 100% (Total: " + total + "%). Silahkan periksa kembali.",
                    icon: "warning"
                });
                return;
            }
            
            if (jpg > 0 && bpg === 0) { Swal.fire({ title: "Perhatian", text: "Bobot Pilihan Ganda tidak boleh 0 jika jumlah soal diisi.", icon: "warning" }); return; }
            if (jpg2 > 0 && bpg2 === 0) { Swal.fire({ title: "Perhatian", text: "Bobot Soal Kompleks tidak boleh 0 jika jumlah soal diisi.", icon: "warning" }); return; }
            if (jjod > 0 && bjod === 0) { Swal.fire({ title: "Perhatian", text: "Bobot Menjodohkan tidak boleh 0 jika jumlah soal diisi.", icon: "warning" }); return; }
            if (jisian > 0 && bisian === 0) { Swal.fire({ title: "Perhatian", text: "Bobot Isian Singkat tidak boleh 0 jika jumlah soal diisi.", icon: "warning" }); return; }
            if (jessai > 0 && besai === 0) { Swal.fire({ title: "Perhatian", text: "Bobot Uraian/Essai tidak boleh 0 jika jumlah soal diisi.", icon: "warning" }); return; }
            if (jurutan > 0 && burutan === 0) { Swal.fire({ title: "Perhatian", text: "Bobot Soal Urutan tidak boleh 0 jika jumlah soal diisi.", icon: "warning" }); return; }
            if (jcloze > 0 && bcloze === 0) { Swal.fire({ title: "Perhatian", text: "Bobot Soal Isian Rumpang tidak boleh 0 jika jumlah soal diisi.", icon: "warning" }); return; }

            console.log("data:", $(this).serialize());
            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: base_url + "cbtbanksoal/saveBank",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (data) {
                    if (data.status) {
                        Swal.fire({
                            title: "Sukses",
                            html: 'Bank soal berhasil disimpan',
                            icon: "success",
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "OK"
                        }).then(result => {
                            if (result.value) {
                                window.history.back();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.errors,
                            icon: "error"
                        });
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    Swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        function getGuruMapel(mapel) {
            if (isAdmin) {
                $.ajax({
                    url: base_url + "cbtbanksoal/getgurumapel?id_mapel=" + mapel,
                    type: "GET",
                    success: function (data) {
                        console.log('guru', data);
                        var opts = '';
                        selGuru.html(opts);
                        $.each(data, function (k, v) {
                            var selected = idGuru == k ? 'selected=selected' : '';
                            opts += '<option value="' + k + '" ' + selected + '>' + v + '</option>';
                        });
                        selGuru.html(opts);
                        idGuru = selGuru.val();
                        getKelasLevel(selLevel.val(), selMapel.val());
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                    }
                });
            }
        }

        function getKelasLevel(level, mapel) {
            console.log('id_guru', idGuru);
            console.log('id_level', level);
            console.log('id_mapel', mapel);
            if (idGuru === '' || mapel == null) {
                console.log('id_guru', 'empty');
            } else {
                $.ajax({
                    url: base_url + "cbtbanksoal/getkelaslevel?level=" + level + "&id_guru=" + idGuru + '&mapel=' + mapel,
                    type: "GET",
                    success: function (data) {
                        console.log('kelas', data);
                        // Do NOT re-initialize, just clear content
                        selKelas.empty().trigger('change'); 
                        var kelas = data.kelas;
                        for (let i = 0; i < kelas.length; i++) {
                            var selected = jQuery.inArray(kelas[i].id_kelas, as) > -1;
                            selKelas.append(new Option(kelas[i].kode_kelas, kelas[i].id_kelas, false, selected));
                        }
                        selKelas.trigger('change');
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                    }
                });
            }
        }

        selGuru.on('change', function () {
            idGuru = $(this).val();
            console.log('id_guru_change', idGuru);
            getKelasLevel(selLevel.val(), selMapel.val());
        });

        selLevel.on('change', function () {
            getKelasLevel($(this).val(), selMapel.val());
        });

        selMapel.on('change', function () {
            getGuruMapel($(this).val());
        });

        var valBobotPg = $('#bobot-pg');
        var valBobotPg2 = $('#bobot-pg2');
        var valBobotJodohkan = $('#bobot-jodohkan');
        var valBobotIsian = $('#bobot-isian');
        var valBobotEssai = $('#bobot-essai');
        var valBobotUrutan = $('#bobot-urutan');
        var valBobotCloze = $('#bobot-cloze');
        var totalBobot = $('#total-bobot');
        onChangeValueBobot();

        var valSoalPg = $('#jml-pg');
        var valSoalPg2 = $('#jml-pg2');
        var valSoalJodohkan = $('#jml-jodohkan');
        var valSoalIsian = $('#jml-isian');
        var valSoalEssai = $('#jml-essai');
        var valSoalUrutan = $('#jml-urutan');
        var valSoalCloze = $('#jml-cloze');
        var totalSoal = $('#total-soal');
        onChangeValueJumlah();

        function onChangeValueBobot() {
            const bobotpg = valBobotPg.val() === "" ? 0 : parseInt(valBobotPg.val());
            const bobotpp2 = valBobotPg2.val() === "" ? 0 : parseInt(valBobotPg2.val());
            const bobotjodohkan = valBobotJodohkan.val() === "" ? 0 : parseInt(valBobotJodohkan.val());
            const bobotisian = valBobotIsian.val() === "" ? 0 : parseInt(valBobotIsian.val());
            const bobotessai = valBobotEssai.val() === "" ? 0 : parseInt(valBobotEssai.val());
            const boboturutan = valBobotUrutan.val() === "" ? 0 : parseInt(valBobotUrutan.val());
            const bobotcloze = valBobotCloze.val() === "" ? 0 : parseInt(valBobotCloze.val());

            totalBobot.text((bobotpg + bobotpp2 + bobotjodohkan + bobotisian + bobotessai + boboturutan + bobotcloze) + '');
        }

        function onChangeValueJumlah() {
            const jmlpg = valSoalPg.val() === "" ? 0 : parseInt(valSoalPg.val());
            const jmlpp2 = valSoalPg2.val() === "" ? 0 : parseInt(valSoalPg2.val());
            const jmljodohkan = valSoalJodohkan.val() === "" ? 0 : parseInt(valSoalJodohkan.val());
            const jmlisian = valSoalIsian.val() === "" ? 0 : parseInt(valSoalIsian.val());
            const jmlessai = valSoalEssai.val() === "" ? 0 : parseInt(valSoalEssai.val());
            const jmlurutan = valSoalUrutan.val() === "" ? 0 : parseInt(valSoalUrutan.val());
            const jmlcloze = valSoalCloze.val() === "" ? 0 : parseInt(valSoalCloze.val());

            totalSoal.text((jmlpg + jmlpp2 + jmljodohkan + jmlisian + jmlessai + jmlurutan + jmlcloze) + '');
        }

        valBobotPg.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotPg2.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotJodohkan.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotIsian.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotEssai.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotUrutan.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotCloze.on('change keyup', function () {
            onChangeValueBobot();
        });

        valSoalPg.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalPg2.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalJodohkan.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalIsian.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalEssai.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalUrutan.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalCloze.on('change keyup', function () {
            onChangeValueJumlah();
        });
    });

</script>


