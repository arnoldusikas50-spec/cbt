<main class="app-main bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="alert border border-success alert-default-success">
                        <div class="row">
                            <span class="col-12 mb-2">
                                Jika ada jabatan guru di semester sebelumnya, jabatan dan mata pelajaran yang diampu
                                bisa dicopy ke semester sekarang, dengan catatan:
                                <ul class="mb-1">
                                    <li>
                                        <b>Aksi ini akan mengganti jabatan dan mapel guru</b> yang sudah diatur di
                                        semester sekarang.
                                    </li>
                                    <li>
                                        Pastikan <b>Kode Kelas sudah dibuat</b> dan sama seperti Kode Kelas sebelumnya.
                                    </li>
                                    <li>
                                        Aksi ini tidak akan berjalan sempurna <b>jika nama kode kelas di semester
                                            sebelumnya berbeda dengan kode kelas di semester sekarang.</b>
                                    </li>
                                    <li>
                                        Kode kelas seperti <span class="badge text-sm">7.A</span>, <span
                                            class="badge text-sm">7-A</span>, <span class="badge text-sm">7 A</span>,
                                        <span class="badge text-sm">7_A</span> atau <span
                                            class="badge text-sm">7A</span> akan dianggap sama
                                    </li>
                                </ul>
                            </span>
                            <div class="col-12 text-end">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#beforeModal"
                                    class="btn btn-success btn-sm">
                                    Lihat jabatan sebelumnya
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card my-shadow mb-4">
                        <?= form_open('dataguru/saveJabatan', array('id' => 'editjabatan'), array('id_guru' => $guru->id_guru)) ?>
                        <div class="card-header">
                            <h6 class="card-title text-bold">Edit Jabatan <?= $guru->nama_guru ?></h6>
                            <div class="card-tools ms-auto">
                                <a type="button" href="<?= base_url('dataguru') ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-arrow-repeat"></i> <span
                                        class="d-none d-sm-inline-block ms-1">Reload</span>
                                </a>
                                <button type="submit" id="btn-jabatan" class="btn btn-primary btn-sm">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                //var_dump($guru);
                                $jmapelval = $guru->mapel_kelas;
                                $jks = [];
                                if ($jmapelval != null) {
                                    foreach ($jmapelval as $key => $val) {
                                        $jks[] = $val->id_mapel;
                                    }
                                }

                                $jekstraval = $guru->ekstra_kelas;
                                $jke = [];
                                if ($jekstraval != null) {
                                    foreach ($jekstraval as $key => $val) {
                                        $jke[] = $val->id_ekstra;
                                    }
                                }
                                ?>
                                <div class="col-md-6">
                                    <div class="card card-primary border border-light">
                                        <div class="card-header">
                                            <span class="card-title text-bold">Mengajar</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <label>Mata Pelajaran:</label>
                                                <?php
                                                echo form_dropdown(
                                                    'mapel[]',
                                                    $mapels,
                                                    $jks,
                                                    'id="mapel" class="select2 form-control form-control-sm" multiple="multiple" data-placeholder="Pilih Mapel"'
                                                ); ?>

                                            </div>
                                            <div class="row" id="input-ekstra">
                                                <label>Ekstrakurikuler:</label>
                                                <?php
                                                echo form_dropdown(
                                                    'ekstra[]',
                                                    $ekskul,
                                                    $jke,
                                                    'id="ekstra" class="select2 form-control form-control-sm" multiple="multiple" data-placeholder="Pilih Ekstrakurikuler"'
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-info border border-light">
                                        <div class="card-header">
                                            <span id="keterangan" class="card-title text-bold">Tentukan Kelas
                                                Mapel</span>
                                        </div>
                                        <div class="card-body" id="input-mapel"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-primary border border-light">
                                        <div class="card-header">
                                            <span id="keterangan" class="card-title text-bold">Jabatan</span>
                                            <button type="button" data-bs-toggle="modal"
                                                data-bs-target="#createJabatanModal"
                                                class="card-tools btn btn-xs alert-default-success ms-auto"><i
                                                    class="bi bi-plus"></i><span
                                                    class="d-none d-sm-inline-block ms-1">Tambah Data</span>
                                            </button>
                                        </div>
                                        <div class="card-body" id="input-jabatan">
                                            <div class="row mb-4">
                                                <label>Jabatan</label>
                                                <?php
                                                echo form_dropdown(
                                                    'level',
                                                    $levels,
                                                    $guru->id_level,
                                                    'id="level" class="form-control form-control-sm" required'
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <?= form_open('dataguru/saveJabatan', array('id' => 'copyjabatan'), array('id_guru' => $guru->id_guru, 'copy' => 'copy')) ?>
    <div class="modal fade" id="beforeModal" tabindex="-1" role="dialog" aria-labelledby="beforeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="beforeModalLabel">Jabatan Sebelumnya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <?php if (isset($before)):
                            $kls_lalu = $before['kelass'] ?? [];
                            $guru2 = $before['guru'] ?? [];
                            if ($guru2->mapel_kelas != null || $guru2->ekstra_kelas != null):
                                $jks2 = [];
                                $jke2 = [];
                                ?>
                                        <div class="d-none">
                                            <?php
                                            foreach ($guru2->mapel_kelas as $val) {
                                                array_push($jks2, $val->id_mapel);

                                                $idklsm = [];
                                                foreach ($val->kelas_mapel as $ids) {
                                                    array_push($idklsm, $ids->kelas);
                                                }

                                                echo form_dropdown(
                                                    'kelasmapel' . $val->id_mapel . '[]',
                                                    $kls_lalu,
                                                    $idklsm,
                                                    'class="guru2 select2 form-control form-control-sm" multiple="multiple"'
                                                );
                                                echo '<input type="hidden" name="nama_mapel' . $val->id_mapel . '" value="' . $val->nama_mapel . '">';
                                            }
                                            foreach ($guru2->ekstra_kelas as $val) {
                                                array_push($jke2, $val->id_ekstra);

                                                $idklse = [];
                                                foreach ($val->kelas_ekstra as $ids) {
                                                    array_push($idklse, $ids->kelas);
                                                }

                                                echo form_dropdown(
                                                    'kelasekstra' . $val->id_ekstra . '[]',
                                                    $kls_lalu,
                                                    $idklse,
                                                    'class="guru2 select2 form-control form-control-sm" multiple="multiple"'
                                                );
                                                echo '<input type="hidden" name="nama_ekstra' . $val->id_ekstra . '" value="' . $val->nama_ekstra . '">';
                                            }
                                            echo form_dropdown(
                                                'mapel[]',
                                                $mapels,
                                                $jks2,
                                                'class="guru2 select2 form-control form-control-sm" multiple="multiple"'
                                            );
                                            echo form_dropdown(
                                                'ekstra[]',
                                                $ekskul,
                                                $jke2,
                                                'class="guru2 select2 form-control form-control-sm" multiple="multiple"'
                                            );
                                            echo form_dropdown(
                                                'level',
                                                $levels,
                                                $guru2->id_level,
                                                'class="guru2 select2 form-control form-control-sm"'
                                            );
                                            echo form_dropdown(
                                                'kelas_wali',
                                                $kls_lalu,
                                                $guru2->id_kelas,
                                                'class="guru2 select2 form-control form-control-sm"'
                                            );
                                            ?>
                                        </div>
                                        <span>Jabatan:</span>
                                        <br>
                                        <span class="text-lg"><?= $levels[$guru2->id_level] ?></span>
                                        <br>
                                        <br>
                                        <span>Pengampu:</span>
                                        <table class="table table-bordered table-bordered">
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th>Mata Pelajaran</th>
                                                <th class="text-center">Kelas</th>
                                            </tr>
                                            <?php
                                            $nn = 1;
                                            foreach ($guru2->mapel_kelas as $mapel):
                                                $all_kelas_mapel = $mapel->kelas_mapel;
                                                $kls_guru_mapel = '';
                                                foreach ($all_kelas_mapel as $kls_mpl) {
                                                    if (isset($kls_lalu[$kls_mpl->kelas]))
                                                        $kls_guru_mapel .= '<span class="badge bg-primary border">' . $kls_lalu[$kls_mpl->kelas] . '</span> ';
                                                }
                                                ?>
                                                    <tr>
                                                        <td class="text-center"><?= $nn ?></td>
                                                        <td><?= $mapel->nama_mapel ?></td>
                                                        <td class="text-center">
                                                            <?= $kls_guru_mapel ?>
                                                        </td>
                                                    </tr>
                                                    <?php $nn++; endforeach; ?>
                                            <?php
                                            foreach ($guru2->ekstra_kelas as $ekstra):
                                                $all_kelas_ekstra = $ekstra->kelas_ekstra;
                                                $kls_guru_ekstra = '';
                                                foreach ($all_kelas_ekstra as $kls_eks) {
                                                    if (isset($kls_lalu[$kls_eks->kelas]))
                                                        $kls_guru_ekstra .= '<span class="badge bg-primary border">' . $kls_lalu[$kls_eks->kelas] . '</span> ';
                                                }
                                                ?>
                                                    <tr>
                                                        <td class="text-center align-middle"><?= $nn ?></td>
                                                        <td class="align-middle"><?= $ekstra->nama_ekstra ?></td>
                                                        <td class="text-center align-middle">
                                                            <?= $kls_guru_ekstra ?>
                                                        </td>
                                                    </tr>
                                                    <?php $nn++; endforeach; ?>
                                        </table>
                                <?php else: ?>
                                        <div class="alert alert-default-danger shadow" role="alert">
                                            Tidak ada jabatan sebelumnya
                                        </div>
                                <?php endif; ?>
                        <?php else: ?>
                                <div class="alert alert-default-danger" role="alert">
                                    Tidak ada jabatan sebelumnya
                                </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger float-end" data-bs-dismiss="modal">Batal</button>
                    <?php if ($guru2->mapel_kelas != null || $guru2->ekstra_kelas != null): ?>
                            <button type="submit" class="btn btn-success float-end">
                                <i class="bi bi-copy"></i> Copy
                            </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?= form_close(); ?>

    <?= form_open('dataguru/addJabatan', array('id' => 'create')) ?>
    <div class="modal fade" id="createJabatanModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Level <b>Jabatan</b></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert border border-danger alert-default-warning">
                        Nomor 1 sampai 5 <b>jangan diubah dan jangan diganti</b>.
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <button type="button" class="btn btn-info add-new float-end"><i class="bi bi-plus"></i>
                                Tambah</button>
                        </div>
                    </div>
                    <div class="table-responsive" id="table-editable">
                        <table class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 80px">No</th>
                                    <th class="align-middle">Jabatan</th>
                                    <th class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($levels as $id => $level):
                                    $btn_dis = $id <= 5 ? 'btn-disabled' : '';
                                    $dis = $id <= 5 ? 'disabled' : '';
                                    if ($id != ''): ?>
                                                <tr>
                                                    <td class="text-center align-middle p-1 id-level"><?= $id ?></td>
                                                    <td class="align-middle p-1"><?= $level ?></td>
                                                    <td class="text-center align-middle p-1">
                                                        <button type="button" class="add btn <?= $btn_dis ?>" title="Add"
                                                            style="display: none;" <?= $dis ?>><i
                                                                class="bi bi-check text-success"></i></button>
                                                        <button type="button" class="edit btn <?= $btn_dis ?>" title="Edit" <?= $dis ?>><i class="bi bi-pencil text-warning"></i></button>
                                                        <button type="button" class="delete btn <?= $btn_dis ?>" title="Delete" <?= $dis ?>><i class="bi bi-trash text-danger"></i></button>
                                                    </td>
                                                </tr>
                                        <?php endif; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <script type="text/javascript">
        const guru_id = '<?= $guru->id_guru ?>';
        const kelas_id = '<?= $guru->id_kelas ?>';
        const level_id = '<?= $guru->id_level ?>';
        const mapel_guru = '<?= json_encode($guru->mapel_kelas ?? '[]') ?>';
        const ekstra_guru = '<?= json_encode($guru->ekstra_kelas ?? '[]') ?>';
        const guru_before = JSON.parse('<?= str_replace("'", "\'", json_encode($guru2 ?? '[]')) ?>');

        let kelas;
        const mapelObj = [];
        let mapelGuru;
        const ekstraObj = [];
        let ekstraGuru;
        let mapelTerisi, ekstraTerisi, jabatanTerisi;
        let changed = false;

        // Custom Select2 Styling Helpers
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

        var select2Config = {
            width: '100%',
            tags: true, // Keeping tags: true as per existing code for dynamic dropdowns
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
                    'background-color': '#ffffff', // White background for cleaner look
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
        };

        $(document).ready(function () {
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
                    /* Remove the dot artifact */
                    '.select2-container .select2-selection--multiple .select2-selection__rendered::after { content: none !important; display: none !important; }' +
                    '</style>').appendTo('head');
            }

            const actions = '<button type="button" class="add btn"><i class="bi bi-check text-success"></i></button>' +
                '<button type="button" class="edit btn"><i class="bi bi-pencil text-warning"></i></button>' +
                '<button type="button" class="delete btn"><i class="bi bi-trash text-danger"></i></button>';
            //$("#table-editable td:last-child").html();

            // Append table with add row form on add new button click
            $(".add-new").click(function () {
                $(this).attr("disabled", "disabled");
                const index = $("#table-editable table tbody tr:last-child").index();
                const valId = $("#table-editable table tbody tr").eq(index).find('.id-level').text();
                const newId = Number(valId) + 1;
                const row = '<tr>' +
                    '<td class="text-center align-middle p-1 id-level">' +
                    '<input type="number" class="form-control" name="id_level" value="' + newId + '" readonly>' +
                    '</td>' +
                    '<td class="align-middle p-1">' +
                    '<input type="text" class="form-control" name="level">' +
                    '</td>' +
                    '<td class="text-center align-middle p-1">' + actions + '</td>' +
                    '</tr>';
                $("#table-editable table").append(row);
                $("#table-editable table tbody tr").eq(index + 1).find(".edit").toggle();
            });
            // Add row on add button click
            $(document).on("click", ".add", function () {
                let empty = false;
                const input = $(this).parents("tr").find('input');
                input.each(function () {
                    if (!$(this).val()) {
                        $(this).addClass("error");
                        empty = true;
                    } else {
                        $(this).removeClass("error");
                    }
                });
                $(this).parents("tr").find(".error").first().focus();
                if (!empty) {
                    let spost = '&mode=1';
                    input.each(function (ind, v) {
                        if (ind === 0) {
                            spost += '&id_level=' + $(this).val();
                        } else {
                            spost += '&level=' + $(this).val();
                        }
                        $(this).parent("td").html($(this).val());
                    });
                    $(this).parents("tr").find(".add, .edit").toggle();
                    sendJabatan(spost);
                }
            });
            // Edit row on edit button click
            $(document).on("click", ".edit", function () {
                $(this).parents("tr").find("td:not(:last-child)").each(function (ind, v) {
                    if (ind === 0) {
                        $(this).html('<input type="number" class="form-control" value="' + $(this).text() + '" readonly>');
                    } else {
                        $(this).html('<input type="text" class="form-control" value="' + $(this).text() + '">');
                    }
                });
                $(this).parents("tr").find(".add, .edit").toggle();
                $(".add-new").attr("disabled", "disabled");
            });
            // Delete row on delete button click
            $(document).on("click", ".delete", function () {
                $(this).parents("tr").remove();
                const sid = $(this).parents("tr").eq(0).find('.id-level').text();
                sendJabatan('&mode=2&id_level=' + sid);
            });

            $("#createJabatanModal").on("hidden.bs.modal", function () {
                if (changed) window.location.reload();
            });

            $('#mapel').select2(select2Config);
            $('#level').select2(); // Level seems to be single select, usually okay to leave, or can apply same style if desired.
            $('#ekstra').select2(select2Config);
            $('.guru2').select2({ width: '100%' });

            $.ajax({
                url: base_url + "dataguru/getDataKelas",
                type: "GET",
                success: function (data) {
                    console.log('kelas', data);
                    mapelTerisi = data.mpl_terisi;
                    ekstraTerisi = data.eks_terisi;
                    jabatanTerisi = data.jabatan;
                    kelas = data.kelas;
                    kelas.sort(sortByKode);
                    kelas.sort(sortByLevel);

                    createDropdownKelasMapel();
                    createDropdownKelasEkstra();
                    createDropdownKelasWali();
                }, error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                    showDangerToast('Data error.');
                }
            });

            $('form input, form select').on('change', function () {
                $(this).closest('.mb-3').removeClass('has-error');
                $(this).nextAll('.help-block').eq(0).text('');
            });

            $('form#editjabatan').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let btn = $('#btn-jabatan');
                btn.attr('disabled', 'disabled').text('Process...');
                //console.log($(this).serialize());
                $.ajax({
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    type: 'POST',
                    success: function (response) {
                        console.log(response);
                        Swal.fire({
                            "title": response.status ? "Berhasil" : "Gagal",
                            "text": response.msg,
                            "icon": response.status ? "success" : "error"
                        }).then(result => {
                            window.location.href = base_url + 'dataguru';
                        });
                        btn.removeAttr('disabled').text('Simpan');
                    },
                    error: function (xhr, error, status) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $('#mapel').on('select2:select', function (e) {
                const data = e.params.data;
                mapelObj.push(data);

                const inputgroup = $('#input-mapel');

                const div1 = $('<div>', { class: "row mb-4 addmapel", id: data.id });
                const hiden = $('<input>', { type: "hidden", name: "nama_mapel" + data.id, value: data.text });
                hiden.appendTo(div1);

                const spangroup = $('<label>', { class: "", text: data.text });
                const sel = $('<select>', {
                    id: "kelasmapel" + data.id, name: "kelasmapel" + data.id + "[]", class: "select2 form-control selectmapel",
                    multiple: 'multiple', required: 'required'
                });

                div1.append(spangroup);
                div1.append(sel);
                div1.appendTo(inputgroup);

                if (kelas.length === 0) {
                    sel.append($('<option></option>')
                        .attr('value', '')
                        .attr('disabled', true)
                        .text('Belum ada kelas'));
                }
                $.each(kelas, function (key, entry) {
                    console.log('kls', key)
                    const terisi = [];
                    $.each(mapelTerisi[data.id], function (idg, valg) {
                        const ada = guru_id != idg && inArrayKelas(entry.id_kelas, valg['kelas']);
                        const mengisi = ada ? ' - - ( ' + valg['guru'] + ' )' : '';
                        if (ada) {
                            terisi.push({ ada: ada, kelas: entry.id_kelas, guru: mengisi });
                        }
                    });
                    const adaGuru = terisi.length > 0 ? terisi[0].guru : '';
                    sel.append($('<option></option>')
                        .attr('value', entry.id_kelas)
                        .attr('disabled', terisi.length > 0)
                        .text(entry.nama_kelas + adaGuru));
                });

                const selectedMapel = [];
                $.each(mapelGuru, function (keym, entrym) {
                    //console.log(entrym.id_mapel, data.id);
                    if (entrym.id_mapel === data.id) {
                        $.each(entrym.kelas_mapel, function (k, en) {
                            selectedMapel.push(en.kelas);
                        });
                    }
                });
                //console.log(selectedMapel);
                $('#kelasmapel' + data.id).val(selectedMapel);
                $(".selectmapel").select2(select2Config);

            });

            $('#mapel').on('select2:unselect', function (e) {
                const data = e.params.data;
                for (let i = 0; i < mapelObj.length; i++)
                    if (mapelObj[i].id && mapelObj[i].id === data.id) {
                        $("div").remove("#" + data.id + "");
                        break;
                    }
            });

            $('#ekstra').on('select2:select', function (e) {
                const data = e.params.data;
                ekstraObj.push(data);

                const inputgroup = $('#input-mapel');

                const div1 = $('<div>', { class: "row mb-4 addekstra", id: data.id });
                const hiden = $('<input>', { type: "hidden", name: "nama_ekstra" + data.id, value: data.text });
                hiden.appendTo(div1);

                //var div2 = $('<div>',  {class : ""});
                const spangroup = $('<label>', { class: "", text: data.text });
                const sel = $('<select>', {
                    id: "kelasekstra" + data.id,
                    name: "kelasekstra" + data.id + "[]",
                    class: "select2 form-control selectekstra",
                    multiple: 'multiple',
                    required: 'required'
                });

                //div2.append(spangroup);
                div1.append(spangroup);
                div1.append(sel);
                div1.appendTo(inputgroup);

                if (kelas.length === 0) {
                    sel.append($('<option></option>')
                        .attr('value', '')
                        .attr('disabled', true)
                        .text('Belum ada kelas'));
                }
                $.each(kelas, function (key, entry) {
                    let terisi = [];
                    $.each(ekstraTerisi[data.id], function (idg, valg) {
                        const ada = guru_id != idg && inArrayKelas(entry.id_kelas, valg['kelas']);
                        const mengisi = ada ? ' - - ( ' + valg['guru'] + ' )' : '';
                        if (ada) {
                            terisi.push({ ada: ada, kelas: entry.id_kelas, guru: mengisi });
                        }
                    });
                    const adaGuru = terisi.length > 0 ? terisi[0].guru : '';
                    sel.append($('<option></option>')
                        .attr('disabled', terisi.length > 0)
                        .attr('value', entry.id_kelas)
                        .text(entry.nama_kelas + adaGuru));
                });

                const selectedEkstra = [];
                $.each(ekstraGuru, function (keym, entrym) {
                    //console.log(entrym.id_ekstra, data.id);
                    if (entrym.id_ekstra === data.id) {
                        $.each(entrym.kelas_ekstra, function (k, en) {
                            selectedEkstra.push(en.kelas);
                        });
                    }
                });
                //console.log(selectedEkstra);
                $('#kelasekstra' + data.id).val(selectedEkstra);
                $(".selectekstra").select2(select2Config);
            });

            $('#ekstra').on('select2:unselect', function (e) {
                const data = e.params.data;
                //console.log(data);
                for (let i = 0; i < ekstraObj.length; i++)
                    if (ekstraObj[i].id && ekstraObj[i].id === data.id) {
                        //ekstraObj.splice(i, 1);
                        $("div").remove("#" + data.id + "");
                        break;
                    }
                //console.log("ekstraObj", ekstraObj);

                //if (!$(".addmapel").length && !$(".addekstra").length) {
                //    $('#keterangan').addClass('d-none');
                //}
            });

            $('#kelas-wali').select2({
                dropdownAutoWidth: true,
                width: 'auto'
            });

            $('#level').on('select2:select', function (e) {
                const data = e.params.data;
                //console.log(data);

                if (data.id === "4") {
                    const inputgroup = $('#input-jabatan');

                    const div1 = $('<div>', { class: "row mb-4 addkelas", id: "input-group-walikelas" });
                    //var div2 = $('<div>',  {class : ""});
                    const spangroup = $('<label>', { class: "", text: "Kelas" });
                    const sel = $('<select>', {
                        name: "kelas_wali",
                        class: "select2 form-control selectkelas",
                        required: 'required'
                    });
                    //div2.append(spangroup);
                    div1.append(spangroup);
                    div1.append(sel);
                    div1.appendTo(inputgroup);

                    $.each(kelas, function (key, entry) {
                        let nguru = '';
                        let selected = '';
                        if (jabatanTerisi[4] !== undefined && jabatanTerisi[4][entry.id_kelas] !== undefined) {
                            const idg = jabatanTerisi[4][entry.id_kelas].id;
                            nguru = guru_id != idg ? ' - - ( ' + jabatanTerisi[4][entry.id_kelas].nama + ' )' : '';
                            selected = entry.id_kelas == kelas_id ? 'selected="selected" ' : '';
                        }
                        const disabl = nguru !== '' ? 'disabled="disabled"' : '';
                        sel.append($('<option value="' + entry.id_kelas + '" ' + selected + disabl + '>' + entry.nama_kelas + nguru + '</option>'));
                    });
                    sel.prepend($('<option value="" selected="selected" disabled>Pilih Kelas</option>'));

                    $(".selectkelas").select2({ tags: true });
                } else {
                    $("div").remove("#input-group-walikelas");
                }
            });

            $('#copyjabatan').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $('#beforeModal').modal('hide').data('bs.modal', null);

                console.log($(this).serialize());
                $.ajax({
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    type: 'POST',
                    success: function (response) {
                        console.log(response);
                        Swal.fire({
                            "title": response.status ? "Berhasil" : "Gagal",
                            "text": response.msg,
                            "icon": response.status ? "success" : "error"
                        }).then(result => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr, error, status) {
                        console.log(xhr.responseText);
                    }
                });
            });

        });

        function inArrayKelas(val, array) {
            const found = $.inArray(val, array);
            return found >= 0;
        }

        function submitajax(url, data, msg, btn) {
            $.ajax({
                url: url,
                data: data,
                type: 'POST',
                success: function (response) {
                    if (response.status) {
                        Swal.fire({
                            "title": "Berhasil",
                            "text": msg,
                            "type": "success"
                        });
                    } else {
                        if (response.errors) {
                            $.each(response.errors, function (key, val) {
                                $('[name="' + key + '"]').closest('.mb-3').addClass('has-error');
                                $('[name="' + key + '"]').nextAll('.help-block').eq(0).text(val);
                                if (val === '') {
                                    $('[name="' + key + '"]').closest('.mb-3').removeClass('has-error');
                                    $('[name="' + key + '"]').nextAll('.help-block').eq(0).text('');
                                }
                            });
                        }
                        if (response.msg) {
                            Swal.fire({
                                "title": "Gagal",
                                "text": "Password lama tidak benar",
                                "type": "error"
                            });
                        }
                    }
                    btn.removeAttr('disabled').text('Ganti Password');
                }
            });
        }

        function createDropdownKelasMapel() {
            mapelGuru = JSON.parse(mapel_guru);

            const inputgroup = $('#input-mapel');

            console.log('mpl', mapelGuru);
            $.each(mapelGuru, function (keym, entrym) {
                const div1 = $('<div>', { class: "row mb-4 addmapel", id: entrym.id_mapel });
                const hiden = $('<input>', { type: "hidden", name: "nama_mapel" + entrym.id_mapel, value: entrym.nama_mapel });
                hiden.appendTo(div1);

                //var div2 = $('<div>',  {class : ""});
                const spangroup = $('<label>', { class: "", text: entrym.nama_mapel });
                const sel = $('<select>', {
                    id: "kelasmapel" + entrym.id_mapel,
                    name: "kelasmapel" + entrym.id_mapel + "[]",
                    class: "select2 form-control selectmapel",
                    multiple: 'multiple',
                    required: 'required'
                });

                //div2.append(spangroup);
                div1.append(spangroup);
                div1.append(sel);
                div1.appendTo(inputgroup);

                if (kelas.length === 0) {
                    sel.append($('<option></option>')
                        .attr('value', '')
                        .attr('disabled', true)
                        .text('Belum ada kelas'));
                }
                $.each(kelas, function (key, entry) {
                    const terisi = [];
                    $.each(mapelTerisi[entrym.id_mapel], function (idg, valg) {
                        const ada = guru_id != idg && inArrayKelas(entry.id_kelas, valg['kelas']);
                        const mengisi = ada ? ' - - ( ' + valg['guru'] + ' )' : '';
                        if (ada) {
                            terisi.push({ ada: ada, kelas: entry.id_kelas, guru: mengisi });
                        }
                    });
                    const adaGuru = terisi.length > 0 ? terisi[0].guru : '';
                    sel.append($('<option></option>')
                        .attr('value', entry.id_kelas)
                        .attr('disabled', terisi.length > 0)
                        .text(entry.nama_kelas + adaGuru));
                });

                const item = {};
                item["id"] = entrym.id_mapel;
                mapelObj.push(item);

                const selectedMapel = [];
                $.each(entrym.kelas_mapel, function (k, en) {
                    selectedMapel.push(en.kelas);
                });
                $('#kelasmapel' + entrym.id_mapel).val(selectedMapel);
            });

            $(".selectmapel").select2(select2Config);
        }

        function createDropdownKelasEkstra() {
            ekstraGuru = JSON.parse(ekstra_guru);

            const inputgroup = $('#input-mapel');

            $.each(ekstraGuru, function (keym, entrym) {
                const div1 = $('<div>', { class: "row mb-4 addekstra", id: entrym.id_ekstra });
                const hiden = $('<input>', { type: "hidden", name: "nama_ekstra" + entrym.id_ekstra, value: entrym.nama_ekstra });
                hiden.appendTo(div1);

                //var div2 = $('<div>',  {class : ""});
                const spangroup = $('<label>', { class: "", text: entrym.nama_ekstra });
                const sel = $('<select>', {
                    id: "kelasekstra" + entrym.id_ekstra,
                    name: "kelasekstra" + entrym.id_ekstra + "[]",
                    class: "select2 form-control selectekstra",
                    multiple: 'multiple',
                    required: 'required'
                });

                //div2.append(spangroup);
                div1.append(spangroup);
                div1.append(sel);
                div1.appendTo(inputgroup);

                if (kelas.length === 0) {
                    sel.append($('<option></option>')
                        .attr('value', '')
                        .attr('disabled', true)
                        .text('Belum ada kelas'));
                }
                $.each(kelas, function (key, entry) {
                    const terisi = [];
                    $.each(ekstraTerisi[entrym.id_ekstra], function (idg, valg) {
                        const ada = guru_id != idg && inArrayKelas(entry.id_kelas, valg['kelas']);
                        const mengisi = ada ? ' - - ( ' + valg['guru'] + ' )' : '';
                        if (ada) {
                            terisi.push({ ada: ada, kelas: entry.id_kelas, guru: mengisi });
                        }
                    });
                    const adaGuru = terisi.length > 0 ? terisi[0].guru : '';
                    sel.append($('<option></option>')
                        .attr('disabled', terisi.length > 0)
                        .attr('value', entry.id_kelas)
                        .text(entry.nama_kelas + adaGuru));
                });

                const item = {};
                item["id"] = entrym.id_ekstra;
                ekstraObj.push(item);

                const selectedEkstra = [];
                $.each(entrym.kelas_ekstra, function (k, en) {
                    selectedEkstra.push(en.kelas);
                });
                $('#kelasekstra' + entrym.id_ekstra).val(selectedEkstra);
            });

            $(".selectekstra").select2(select2Config);
        }



        function createDropdownKelasWali() {
            if (level_id === "4") {
                const inputgroup = $('#input-jabatan');

                const div1 = $('<div>', { class: "row mb-4 addkelas", id: "input-group-walikelas" });
                //var div2 = $('<div>',  {class : ""});
                const spangroup = $('<label>', { class: "", text: "Kelas" });
                const sel = $('<select>', {
                    name: "kelas_wali",
                    class: "select2 form-control selectkelas",
                    required: 'required'
                });
                //div2.append(spangroup);
                div1.append(spangroup);
                div1.append(sel);
                div1.appendTo(inputgroup);

                const arrSelect = [];
                $.each(kelas, function (key, entry) {
                    let nguru = '';
                    let selected = [];
                    if (jabatanTerisi[4] !== undefined && jabatanTerisi[4][entry.id_kelas] !== undefined) {
                        const idg = jabatanTerisi[4][entry.id_kelas].id;
                        nguru = guru_id != idg ? ' - - ( ' + jabatanTerisi[4][entry.id_kelas].nama + ' )' : '';
                        selected = entry.id_kelas == kelas_id ? 'selected="selected" ' : '';
                        arrSelect.push(entry.id_kelas == kelas_id);
                    }
                    const disabl = nguru !== '' ? 'disabled="disabled"' : '';
                    sel.append($('<option value="' + entry.id_kelas + '" ' + selected + disabl + '>' + entry.nama_kelas + nguru + '</option>'));
                });
                const isSelected = inArrayKelas(true, arrSelect) ? '' : 'selected="selected"';
                sel.prepend($('<option value="" ' + isSelected + ' disabled>Pilih Kelas</option>'));

                $(".selectkelas").select2({ tags: true });
            }
        }

        function sortByLevel(a, b) {
            const aID = a.level_id.toLowerCase();
            const bID = b.level_id.toLowerCase();
            return ((aID < bID) ? -1 : ((aID > bID) ? 1 : 0));
        }

        function sortByKode(a, b) {
            const aID = a.nama_kelas.toLowerCase();
            const bID = b.nama_kelas.toLowerCase();
            return ((aID < bID) ? -1 : ((aID > bID) ? 1 : 0));
        }

        function sendJabatan(data) {
            $(".add-new").html('<i class="spinner-border spinner-border-sm"></i> Saving');
            console.log($('#create').serialize() + data);
            $.ajax({
                url: $('#create').attr('action'),
                data: $('#create').serialize() + data,
                type: 'POST',
                success: function (response) {
                    console.log(response);
                    $(".add-new").removeAttr("disabled");
                    $(".add-new").html('<i class="bi bi-plus"></i> Tambah');
                    if (response.success) {
                        changed = true;
                        showSuccessToast(response.msg);
                    } else {
                        showDangerToast(response.msg);
                    }
                },
                error: function (xhr, error, status) {
                    console.log(xhr.responseText);
                    showDangerToast(xhr.responseText);
                }
            });
        }
    </script>