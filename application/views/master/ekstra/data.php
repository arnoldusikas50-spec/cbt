<main class="app-main bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4">
                <div class="card-body">
                    <?php
                    $totalEkskul = is_array($ekskul) ? count($ekskul) : (is_object($ekskul) ? count((array) $ekskul) : 0);
                    $totalKelas = is_array($kelas) ? count($kelas) : 0;
                    ?>
                    <div class="mb-4">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Pengelolaan Ekstrakurikuler</h5>
                                <div class="text-muted">Atur daftar ekskul serta distribusinya ke tiap kelas</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row">
                                <span class="badge bg-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Ekskul:
                                    <?= $totalEkskul ?></span>
                                <span class="badge bg-info px-3 py-2 text-sm">Total Kelas: <?= $totalKelas ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center mb-4 gap-2">
                        <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </button>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createEkstraModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i> Tambah Ekskul
                        </button>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Panduan Pengelolaan
                            Ekskul</h5>
                        <ul class="ps-3 mb-0">
                            <li>Gunakan tombol <span class="badge bg-primary">Tambah Ekskul</span> untuk menambahkan
                                kegiatan baru.</li>
                            <li>Atur kelas yang mengikuti ekskul melalui pilihan multi-select pada panel sebelah kanan.
                            </li>
                            <li>Klik <b>Reload</b> setelah perubahan untuk memastikan data terbaru ditampilkan.</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border border-primary">
                                <div class="card-header border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">Daftar Eskul</h6>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive mb-0">
                                        <table id="tableEkstra"
                                            class="w-100 table table-striped table-bordered table-hover mb-0 dataTable">
                                            <thead>
                                                <tr>
                                                    <th class="text-center align-middle border-0">No.</th>
                                                    <th class="border-0">Ekstrakurikuler</th>
                                                    <th class="border-0">Kode</th>
                                                    <th class="text-center align-middle border-0" style="width: 100px">
                                                        Aksi</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= form_open('save', array('id' => 'save')) ?>
                            <div class="card h-100 border border-info">
                                <div class="card-header border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">Tentukan Kelas Ekskul</h6>
                                        <button type="submit" class="btn btn-success btn-sm shadow-sm">
                                            <i class="bi bi-save me-1"></i>Simpan
                                        </button>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <?php
                                    if ($totalKelas > 0):
                                        foreach ($kelas as $key => $kls):
                                            $ids_selected = [];
                                            if ($ekskul_kelas[$key] != null) {
                                                $ids = $ekskul_kelas[$key][0]->ekstra;
                                                foreach ($ids as $id) {
                                                    $ids_selected[] = $id->ekstra;
                                                }
                                            }

                                            ?>
                                            <div class="mb-3">
                                                <label class="font-weight-semibold">Kelas <?= $kls ?></label>
                                                <?php
                                                echo form_dropdown(
                                                    'ekskul' . $key . '[]',
                                                    $ekskul,
                                                    $ids_selected,
                                                    'id="ekskul' . $key . '" class="select2 form-control form-control-sm" multiple="multiple" data-placeholder="Pilih Ekskul"'
                                                ); ?>
                                            </div>
                                        <?php endforeach;
                                    else: ?>
                                        <div class="alert alert-warning" role="alert">Belum ada data kelas</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <?= form_open('create', array('id' => 'create')) ?>
    <div class="modal fade" id="createEkstraModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Ekstrakurikuler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Ekstrakurikuler*</label>
                        <div class="col-md-9">
                            <input type="text" id="createnama" name="nama_ekstra" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Kode*</label>
                        <div class="col-md-9">
                            <input type="text" id="createkode" name="kode_ekstra" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <?= form_open('update', array('id' => 'update')) ?>
    <div class="modal fade" id="editEkstraModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Ekstrakurikuler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Ekstrakurikuler*</label>
                        <div class="col-md-9">
                            <input type="text" id="namaEdit" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Kode*</label>
                        <div class="col-md-9">
                            <input type="text" id="kodeEdit" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdEkstra" class="form-control">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>


    <script>
        var kelases = JSON.parse('<?= json_encode($kelas) ?>');
        var table;

        function deleteItem(id) {
            //console.log(id);
            Swal.fire({
                title: "Anda yakin?",
                text: "mapel ini akan dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "dataekstra/delete/" + id,
                        success: function (data) {
                            console.log(data);
                            if (data.status) {
                                showSuccessToast(data.message);
                                reload_ajax()
                            } else {
                                //showDangerToast(data.message);
                                Swal.fire({
                                    title: "Gagal",
                                    html: data.total,
                                    icon: "error"
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            showDangerToast('Data error');
                        }
                    });
                }
            });
        }

        $(document).ready(function () {
            ajaxcsrf();

            $('.select2').select2({ width: '100%', });

            var arrKelas = [];
            $.each(kelases, function (key, entry) {
                var item = {};
                item['kls_id'] = key;
                arrKelas.push(item);
            });
            console.log(arrKelas);

            table = $("#tableEkstra").DataTable({
                initComplete: function () {
                    var api = this.api();
                    $("#tableEkstra_filter input")
                        .off(".DT")
                        .on("keyup.DT", function (e) {
                            api.search(this.value).draw();
                        });
                },
                oLanguage: {
                    sProcessing: "loading..."
                },
                searching: false,
                info: false,
                paging: false,
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: base_url + "dataekstra/read",
                    dataType: "json",
                    type: "POST"
                    //data: csrf
                },
                columns: [
                    {
                        data: "id_ekstra",
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "nama_ekstra",
                    },
                    {
                        data: "kode_ekstra",
                    }
                ],
                columnDefs: [
                    {
                        searchable: false,
                        targets: 3,
                        className: "text-center",
                        data: {
                            id_ekstra: "id_ekstra",
                            nama_ekstra: "nama_ekstra",
                            kode_ekstra: "kode_ekstra"
                        },
                        render: function (data, type, row, meta) {
                            return `<div class="d-flex justify-content-center gap-1">
                                    <a class="btn btn-sm btn-info shadow-sm editRecord" data-bs-toggle="modal" data-bs-target="#editEkstraModal" data-id='${data.id_ekstra}' data-nama='${data.nama_ekstra}' data-kode='${data.kode_ekstra}' title="Edit">
                                        <i class="bi bi-pencil text-white"></i>
                                    </a>
                                    <a onclick="deleteItem(${data.id_ekstra})" class="btn btn-sm btn-danger shadow-sm deleteRecord" data-id='${data.id_ekstra}' title="Hapus">
                                        <i class="bi bi-trash text-white"></i>
                                    </a>
                                </div>`;
                        }
                    }
                ],
                order: [[1, "asc"]],
                rowId: function (a) {
                    return a;
                },
                rowCallback: function (row, data, iDisplayIndex) {
                    var info = this.fnPagingInfo();
                    var page = info.iPage;
                    var length = info.iLength;
                    var index = page * length + (iDisplayIndex + 1);
                    $("td:eq(0)", row).html(index);
                }
            });

            table
                .buttons()
                .container()
                .appendTo("#tableEkstra_wrapper .col-md-6:eq(1)");

            $("#myModal").on("shown.modal.bs", function () {
                $(':input[name="banyak"]').select();
            });

            $("#tableEkstra tbody").on("click", "tr .check", function () {
                var check = $("#tableEkstra tbody tr .check").length;
                var checked = $("#tableEkstra tbody tr .check:checked").length;
                if (check === checked) {
                    $(".select_all").prop("checked", true);
                } else {
                    $(".select_all").prop("checked", false);
                }
            });

            $('#save').submit(function (e) {
                e.preventDefault();

                console.log("data:", $(this).serialize() + '&kelas=' + JSON.stringify(arrKelas));
                $.ajax({
                    url: base_url + "dataekstra/save",
                    type: "POST",
                    dataType: "JSON",
                    data: $(this).serialize() + '&kelas=' + JSON.stringify(arrKelas),
                    success: function (data) {
                        if (data.status) {
                            console.log(data);
                            showSuccessToast(data.message)
                            //window.location.href = base_url+'dataekstra';
                        } else {
                            showDangerToast('Data tidak tersimpan.');
                        }
                    }, error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        showDangerToast('Data tidak tersimpan.');
                    }
                });
            });

            $('#create').on('submit', function () {
                var nama = $('#createnama').val();
                var kode = $('#createkode').val();
                console.log("data:", $(this).serialize());

                $.ajax({
                    url: base_url + "dataekstra/create",
                    type: "POST",
                    dataType: "JSON",
                    data: $(this).serialize(),
                    success: function (data) {
                        console.log("result", data);
                        window.location.href = base_url + 'dataekstra';
                        /*
                        $('#createEkstraModal').modal('hide').data('bs.modal', null);
                        $('#createEkstraModal').on('hidden', function () {
                            $(this).data('modal', null);  // destroys modal
                        });
                        //showSuccessToast('Data berhasil disimpan.');
                        //table.ajax.reload();
                        */
                    }, error: function (xhr, status, error) {
                        $('#createEkstraModal').modal('hide').data('bs.modal', null);
                        $('#createEkstraModal').on('hidden', function () {
                            $(this).data('modal', null);  // destroys modal
                        });
                        showDangerToast();
                    }
                });
                return false;
            });

            $('#editEkstraModal').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                var nama = $(e.relatedTarget).data('nama');
                var kode = $(e.relatedTarget).data('kode');

                $(e.currentTarget).find('input[id="namaEdit"]').val(nama);
                $(e.currentTarget).find('input[id="kodeEdit"]').val(kode);
                $(e.currentTarget).find('input[id="editIdEkstra"]').val(id);

                var attrId = document.getElementById("editIdEkstra");
                attrId.setAttribute("name", "id_ekstra");

                var attrNama = document.getElementById("namaEdit");
                attrNama.setAttribute("name", "nama_ekstra");

                var attrKode = document.getElementById("kodeEdit");
                attrKode.setAttribute("name", "kode_ekstra");
            });

            $('#update').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var btn = $('#submit');
                btn.attr('disabled', 'disabled').text('Wait...');

                console.log("data", $(this).serialize());

                $.ajax({
                    url: base_url + "dataekstra/update",
                    data: $(this).serialize(),
                    method: 'POST',
                    dataType: "JSON",
                    success: function (data) {
                        console.log("result", jQuery.parseJSON(data));
                        btn.removeAttr('disabled').text('Simpan');
                        $('#editEkstraModal').modal('hide').data('bs.modal', null);
                        $('#editEkstraModal').on('hidden', function () {
                            $(this).data('modal', null);  // destroys modal
                        });

                        showSuccessToast('Data berhasil diupdate.');
                        table.ajax.reload();
                    },
                    error: function (xhr, status, error) {
                        $('#editEkstraModal').modal('hide').data('bs.modal', null);
                        $('#editEkstraModal').on('hidden', function () {
                            $(this).data('modal', null);  // destroys modal
                        });
                        showDangerToast('Error');
                        console.log(xhr);
                    }
                });
            });
        });

        function dismissEdit() {
            var count = $('#tableEkstra tr').length;
            console.log("size", "-->" + count);
            for (var i = 0; i < count; i++) {
                var inputs = document.getElementById('check' + i);
                if (inputs != null) {
                    inputs.checked = false;
                    console.log("id", "-->" + 'check' + i);
                }
            }
        }

    </script>