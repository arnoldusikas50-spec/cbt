<?php

?>

<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools">
                        <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button type="button" data-toggle="modal" data-target="#createSesiModal"
                                class="btn btn-sm btn-primary"><i
                                    class="fas fa-plus"></i><span class="d-none d-sm-inline-block ml-1">Tambah Sesi Ujian</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php $totalSesi = isset($total_sesi) ? $total_sesi : '-'; ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Pengelolaan Sesi Ujian</h5>
                                <div class="text-muted">Tetapkan jadwal sesi, kode, dan durasi istirahat</div>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <span class="badge badge-primary px-3 py-2 text-sm">Total Sesi: <?= $totalSesi ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengelolaan</h5>
                        <ul class="pl-3 mb-0">
                            <li>Tambah sesi baru melalui tombol <span class="badge badge-primary">Tambah Sesi Ujian</span>.</li>
                            <li>Pastikan jam mulai dan berakhir tidak bertabrakan antar sesi.</li>
                            <li>Kolom istirahat bersifat opsional dan tercetak pada daftar hadir/berita acara.</li>
                        </ul>
                    </div>

                    <?= form_open('cbtsesi/delete', array('id' => 'bulk')); ?>
                    <div class="table-responsive">
                        <table id="sesi" class="w-100 table table-striped table-bordered table-hover">
                            <thead>
                            <tr  class="alert alert-default-info">
                                <th width="40" class="align-middle text-center p-0">
                                    <input class="select_all" type="checkbox">
                                </th>
                                <th width="40" class="align-middle text-center p-0">No.</th>
                                <th>Sesi</th>
                                <th>Kode</th>
                                <th>Waktu</th>
                                <th>Istirahat</th>
                                <th class="align-middle text-center p-0">Aksi</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?= form_open('create', array('id' => 'create')) ?>
<div class="modal fade" id="createSesiModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Tambah Sesi Ujian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label for="nama_sesi" class="col-md-4 col-form-label">Sesi Ujian*</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="nama_sesi" placeholder="Nama Sesi" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="kode_sesi" class="col-md-4 col-form-label">Kode Sesi*</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="kode_sesi" placeholder="Kode Sesi Ujian" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Jam Mulai*</label>
                    <div class="col-md-8">
                        <input type="text" id="jamAwal" name="waktu_mulai" class="form-control" autocomplete="off"
                               required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Jam Berakhir*</label>
                    <div class="col-md-8">
                        <input type="text" id="jamAkhir" name="waktu_akhir" class="form-control" autocomplete="off"
                               required>
                    </div>
                </div>
                <div class="alert-default-warning">
                <span class="text-xs"><b>Fitur istirahat sebagai Informasi cetak Daftar Hadir dan Berita Acara saja.</b></span>
                    </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Istirahat [Menit]</label>
                    <div class="col-md-8">
                        <input type="number" id="istirahat" name="istirahat" class="form-control" autocomplete="off">
                        <span class="text-xs">Hanya berlaku untuk informasi cetak Daftar Hadir dan Berita Acara.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
                <button type="reset" class="btn bg-warning text-white">
                    <i class="fa fa-sync mr-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= form_open('update', array('id' => 'update')) ?>
<div class="modal fade" id="editSesiModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Sesi Ujian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Sesi Ujian*</label>
                    <div class="col-md-8">
                        <input type="text" id="namaEdit" name="nama_sesi" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Kode Sesi*</label>
                    <div class="col-md-8">
                        <input type="text" id="kodeEdit" name="kode_sesi" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Jam Mulai*</label>
                    <div class="col-md-8">
                        <input type="text" id="jamAwalEdit" name="waktu_mulai" class="form-control" autocomplete="off"
                               required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Jam Berakhir*</label>
                    <div class="col-md-8">
                        <input type="text" id="jamAkhirEdit" name="waktu_akhir" class="form-control" autocomplete="off"
                               required>
                    </div>
                    </div>
                <div class="alert-default-warning">
                <span class="text-xs"><b>Fitur istirahat sebagai Informasi cetak Daftar Hadir dan Berita Acara saja.</b></span>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Istirahat [Menit]</label>
                    <div class="col-md-8">
                        <input type="number" id="istirahatEdit" name="istirahat" class="form-control" autocomplete="off">
                        <span class="text-xs">Hanya berlaku untuk informasi cetak Daftar Hadir dan Berita Acara.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="editIdSesi" class="form-control">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<script>
    var table;

    $(document).ready(function () {
        ajaxcsrf();

        table = $("#sesi").DataTable({
            initComplete: function () {
                var api = this.api();
                $("#sesi_filter input")
                    .off(".DT")
                    .on("keyup.DT", function (e) {
                        api.search(this.value).draw();
                    });
            },
            dom:
                "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                "<'row'<'toolbar'lfrtip>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            oLanguage: {
                sProcessing: "loading..."
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: base_url + "cbtsesi/data",
                type: "POST"
                //data: csrf
            },
            columns: [
                {
                    data: "id_sesi",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "id_sesi",
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },
                {data: "nama_sesi"},
                {data: "kode_sesi"},
                {data: null, render: function (data, type, row) {
                        return data.waktu_mulai+'  s/d  '+data.waktu_akhir;
                    }}
            ],
            columnDefs: [
                {
                    targets: 5,
                    data: "istirahat",
                    render: function (data, type, row, meta) {
                        return `<div>${data ? data + ' Menit' : 'Tidak ada'}</div>`;
                    }
                },
                {
                    searchable: false,
                    targets: 6,
                    data: {
                        id_sesi: "id_sesi",
                        nama_sesi: "nama_sesi",
                        kode_sesi: "kode_sesi",
                        waktu_mulai: "waktu_mulai",
                        waktu_akhir: "waktu_akhir",
                        istirahat: "istirahat"
                    },
                    render: function (data, type, row, meta) {
                        return `<div class="text-center">
									<a class="ml-1 mr-2 btn btn-warning btn-sm" data-toggle="modal" data-target="#editSesiModal"
									 data-id='${data.id_sesi}' data-nama='${data.nama_sesi}' data-kode='${data.kode_sesi}'
									 data-awal='${data.waktu_mulai}' data-akhir='${data.waktu_akhir}' data-istirahat='${data.istirahat}'>
										<i class="fa fa-pencil-alt"></i> Edit
									</a>
								</div>`;
                    }
                },
                {
                    targets: 0,
                    data: "id_sesi",
                    render: function (data, type, row, meta) {
                        return `<div class="text-center">
									<input name="checked[]" class="check" value="${data}" type="checkbox">
								</div>`;
                    }
                }
            ],
            order: [[4, "asc"]],
            rowId: function (a) {
                return a;
            },
            rowCallback: function (row, data, iDisplayIndex) {
                var info = this.fnPagingInfo();
                var page = info.iPage;
                var length = info.iLength;
                var index = page * length + (iDisplayIndex + 1);
                $("td:eq(1)", row).html(index);
            }
        });

        table
            .buttons()
            .container()
            .appendTo("#sesi_wrapper .col-md-6:eq(1)");

        $("div.toolbar").html(
            '<button id="hapusterpilih" onclick="bulk_delete()" type="button" class="btn btn-danger" data-toggle="tooltip" title="Hapus Terpilh" disabled="disabled">' +
            '<i class="far fa-trash-alt"></i></button>'
        );

        $(".select_all").on("click", function () {
            if (this.checked) {
                $(".check").each(function () {
                    this.checked = true;
                    $(".select_all").prop("checked", true);
                    $('#hapusterpilih').attr('disabled','disabled');
                });
            } else {
                $(".check").each(function () {
                    this.checked = false;
                    $(".select_all").prop("checked", false);
                    $('#hapusterpilih').attr('disabled','disabled');
                });
            }
        });

        $("#sesi tbody").on("click", "tr .check", function () {
            var check = $("#sesi tbody tr .check").length;
            var checked = $("#sesi tbody tr .check:checked").length;
            if (check === checked) {
                $(".select_all").prop("checked", true);
            } else {
                $(".select_all").prop("checked", false);
            }

            if (checked === 0) {
                $('#hapusterpilih').attr('disabled','disabled');
            } else {
                $('#hapusterpilih').removeAttr('disabled');
            }
        });

        $('#create').submit('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $('#createSesiModal').modal('hide').data('bs.modal', null);

            console.log("data:", $(this).serialize());

            $.ajax({
                url: base_url + "cbtsesi/add",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (data) {
                    console.log("result", data);
                    $('#createSesiModal').modal('hide').data('bs.modal', null);
                    $('#createSesiModal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });
                    showSuccessToast('Data berhasil disimpan.');
                    table.ajax.reload();
                }, error: function (xhr, status, error) {
                    $('#createSesiModal').modal('hide').data('bs.modal', null);
                    $('#createSesiModal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });
                    console.log("error", xhr.responseText);
                    showDangerToast("Error");
                }
            });
        });

        $('#editSesiModal').on('show.bs.modal', function (e) {
            var id = $(e.relatedTarget).data('id');
            var nama = $(e.relatedTarget).data('nama');
            var kode = $(e.relatedTarget).data('kode');
            var awal = $(e.relatedTarget).data('awal');
            var akhir = $(e.relatedTarget).data('akhir');
            var istirahat = $(e.relatedTarget).data('istirahat');

            var attrNama = document.getElementById("editIdSesi");
            attrNama.setAttribute("name", "id_sesi");

            $(e.currentTarget).find('input[id="namaEdit"]').val(nama);
            $(e.currentTarget).find('input[id="kodeEdit"]').val(kode);
            $(e.currentTarget).find('input[id="editIdSesi"]').val(id);
            $(e.currentTarget).find('input[id="jamAwalEdit"]').val(awal);
            $(e.currentTarget).find('input[id="jamAkhirEdit"]').val(akhir);
            $(e.currentTarget).find('input[id="istirahatEdit"]').val(istirahat);
        });

        $('#update').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var btn = $('#submit');
            btn.attr('disabled', 'disabled').text('Wait...');

            console.log("data", $(this).serialize());

            $.ajax({
                url: base_url + "cbtsesi/update",
                data: $(this).serialize(),
                method: 'POST',
                dataType: "JSON",
                success: function (data) {
                    $('#editSesiModal').modal('hide').data('bs.modal', null);
                    $('#editSesiModal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });
                    showSuccessToast('Data berhasil diupdate.');
                    table.ajax.reload();
                },
                error: function (xhr, status, error) {
                    $('#editSesiModal').modal('hide').data('bs.modal', null);
                    $('#editSesiModal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });
                    showDangerToast("Error");
                }
            });
        });

        $("#bulk").on("submit", function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $.ajax({
                url: $(this).attr("action"),
                data: $(this).serialize(),
                type: "POST",
                success: function (respon) {
                    $('#hapusterpilih').addClass('d-none');
                    if (respon.status) {
                        swal.fire({
                            title: "Berhasil",
                            text: respon.total + " data berhasil dihapus",
                            icon: "success"
                        });
                    } else {
                        swal.fire({
                            title: "Gagal",
                            text: "Tidak ada data yang dipilih",
                            icon: "error"
                        });
                    }
                    reload_ajax();
                },
                error: function () {
                    $('#hapusterpilih').addClass('d-none');
                    swal.fire({
                        title: "Gagal",
                        text: "Ada data yang sedang digunakan",
                        icon: "error"
                    });
                }
            });
        });

        $('#jamAwalEdit, #jamAkhirEdit, #jamAwal, #jamAkhir').datetimepicker({
            datepicker:false,
            format:'H:i',
            step: 30,
            minTime: '07:00',
            maxTime: '23:00'
        });

    });

    function bulk_delete() {
        if ($("#sesi tbody tr .check:checked").length == 0) {
            swal.fire({
                title: "Gagal",
                text: "Tidak ada data yang dipilih",
                icon: "error"
            });
        } else {
            swal.fire({
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
                }
            });
        }
    }
</script>


