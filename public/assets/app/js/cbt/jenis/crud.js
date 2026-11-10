var table;

$(document).ready(function () {
	ajaxcsrf();

	table = $("#jenis").DataTable({
		initComplete: function () {
			var api = this.api();
			$("#jenis_filter input")
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
			url: base_url + "cbtjenis/data",
			type: "POST"
			//data: csrf
		},
		columns: [
			{
				data: "id_jenis",
				className: "text-center align-middle",
				orderable: false,
				searchable: false
			},
			{
				data: "id_jenis",
				className: "text-center align-middle",
				orderable: false,
				searchable: false
			},
			{
				data: "nama_jenis",
				className: "align-middle",
			},
			{
				data: "kode_jenis",
				className: "text-center align-middle",
			},
			{
				data: "status",
				className: "text-center align-middle",
				orderable: false,
				searchable: false
			}
		],
		columnDefs: [
			{
				// Status column
				targets: 4,
				data: "status",
				render: function (data, type, row, meta) {
					const isActive = data === '1' || data === 1;
					const badgeClass = isActive ? 'bg-success' : 'bg-secondary';
					const statusText = isActive ? 'Aktif' : 'Non-Aktif';
					return `<span class="badge ${badgeClass} cursor-pointer" 
								onclick="toggleStatus('${row.id_jenis}', '${data}')" 
								style="cursor:pointer" title="Klik untuk toggle">
								${statusText}
							</span>`;
				}
			},
			{
				// Aksi column
				searchable: false,
				targets: 5,
				data: {
					id_jenis: "id_jenis",
					nama_jenis: "nama_jenis",
					kode_jenis: "kode_jenis"
				},
				render: function (data, type, row, meta) {
					return `<div class="text-center">
								<a class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editJenisModal" data-id='${data.id_jenis}' data-nama='${data.nama_jenis}' data-kode='${data.kode_jenis}'>
									<i class="bi bi-pencil"></i> Edit
								</a>
							</div>`;
				}
			},
			{
				targets: 0,
				data: "id_jenis",
				render: function (data, type, row, meta) {
					return `<div class="text-center align-middle">
								<input name="checked[]" class="check" value="${data}" type="checkbox">
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
			$("td:eq(1)", row).html(index);
		}
	});

	table
		.buttons()
		.container()
		.appendTo("#jenis_wrapper .col-md-6:eq(1)");

	$("div.toolbar").html(
		'<button id="hapusterpilih" onclick="bulk_delete()" type="button" class="btn btn-danger" data-toggle="tooltip" title="Hapus Terpilh" disabled="disabled">' +
		'<i class="b bi-trash"></i></button>'
		/*
		'<div class="btn-group">' +
		'<button type="button" class="btn btn-default" data-toggle="tooltip" title="Print"><i class="bi bi-print"></i></button>' +
		'<button type="button" class="btn btn-default" data-toggle="tooltip" title="Export As PDF"><i class="bi bi-file-pdf"></i></button>' +
		'<button type="button" class="btn btn-default" data-toggle="tooltip" title="Export As Word"><i class="bi bi-file-word"></i></button>' +
		'<button type="button" class="btn btn-default" data-toggle="tooltip" title="Export As Excel"><i class="bi bi-file-excel"></i></button>' +
		'</div>'
		*/
	);

	$(".select_all").on("click", function () {
		if (this.checked) {
			$(".check").each(function () {
				this.checked = true;
				$(".select_all").prop("checked", true);
				$('#hapusterpilih').removeAttr('disabled');
			});
		} else {
			$(".check").each(function () {
				this.checked = false;
				$(".select_all").prop("checked", false);
				$('#hapusterpilih').attr('disabled', 'disabled');
			});
		}
	});

	$("#jenis tbody").on("click", "tr .check", function () {
		var check = $("#jenis tbody tr .check").length;
		var checked = $("#jenis tbody tr .check:checked").length;
		if (check === checked) {
			$(".select_all").prop("checked", true);
		} else {
			$(".select_all").prop("checked", false);
		}

		if (checked === 0) {
			$('#hapusterpilih').attr('disabled', 'disabled');
		} else {
			$('#hapusterpilih').removeAttr('disabled');
		}
	});

	$('#create').submit('click', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();
		console.log("data:", $(this).serialize());

		$.ajax({
			url: base_url + "cbtjenis/add",
			type: "POST",
			dataType: "JSON",
			data: $(this).serialize(),
			success: function (data) {
				console.log("result", data);
				$('#createJenisModal').modal('hide').data('bs.modal', null);
				$('#createJenisModal').on('hidden', function () {
					$(this).data('modal', null);  // destroys modal
				});
				showSuccessToast('Data berhasil disimpan.');
				table.ajax.reload();
			}, error: function (xhr, status, error) {
				$('#createJenisModal').modal('hide').data('bs.modal', null);
				$('#createJenisModal').on('hidden', function () {
					$(this).data('modal', null);  // destroys modal
				});
				console.log("error", xhr.responseText);
				showDangerToast('Error');
			}
		});
	});

	$('#editJenisModal').on('show.bs.modal', function (e) {
		var id = $(e.relatedTarget).data('id');
		var nama = $(e.relatedTarget).data('nama');
		var kode = $(e.relatedTarget).data('kode');

		$(e.currentTarget).find('input[id="namaEdit"]').val(nama);
		$(e.currentTarget).find('input[id="kodeEdit"]').val(kode);
		$(e.currentTarget).find('input[id="editIdJenis"]').val(id);

		var attrId = document.getElementById("editIdJenis");
		attrId.setAttribute("name", "id_jenis");

		var attrNama = document.getElementById("namaEdit");
		attrNama.setAttribute("name", "nama_jenis");

		var attrKode = document.getElementById("kodeEdit");
		attrKode.setAttribute("name", "kode_jenis");
	});

	$('#update').on('submit', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		var btn = $('#submit');
		btn.attr('disabled', 'disabled').text('Wait...');

		console.log("data", $(this).serialize());

		$.ajax({
			url: base_url + "cbtjenis/update",
			data: $(this).serialize(),
			method: 'POST',
			dataType: "JSON",
			success: function (data) {
				$('#editJenisModal').modal('hide').data('bs.modal', null);
				$('#editJenisModal').on('hidden', function () {
					$(this).data('modal', null);  // destroys modal
				});
				showSuccessToast('Data berhasil diupdate.');
				table.ajax.reload();
			},
			error: function (xhr, status, error) {
				$('#editJenisModal').modal('hide').data('bs.modal', null);
				$('#editJenisModal').on('hidden', function () {
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
					Swal.fire({
						title: "Berhasil",
						text: respon.total + " data berhasil dihapus",
						icon: "success"
					});
				} else {
					Swal.fire({
						title: "Gagal",
						text: "Tidak ada data yang dipilih",
						icon: "error"
					});
				}
				reload_ajax();
			},
			error: function () {
				$('#hapusterpilih').addClass('d-none');
				Swal.fire({
					title: "Gagal",
					text: "Ada data yang sedang digunakan",
					icon: "error"
				});
			}
		});
	});
});

function bulk_delete() {
	if ($("#jenis tbody tr .check:checked").length == 0) {
		Swal.fire({
			title: "Gagal",
			text: "Tidak ada data yang dipilih",
			icon: "error"
		});
	} else {
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
			}
		});
	}
}

/**
 * Toggle status aktif/non-aktif jenis ujian
 */
function toggleStatus(id, currentStatus) {
	ajaxcsrf();
	$.ajax({
		url: base_url + "cbtjenis/toggleStatus",
		type: "POST",
		dataType: "JSON",
		data: {
			id_jenis: id,
			status: currentStatus
		},
		success: function (data) {
			if (data.success) {
				showSuccessToast(data.message);
				table.ajax.reload(null, false);
			} else {
				showDangerToast(data.message);
			}
		},
		error: function (xhr, status, error) {
			console.log("error", xhr.responseText);
			showDangerToast('Error mengubah status');
		}
	});
}

function reload_ajax() {
	table.ajax.reload(null, false);
}
