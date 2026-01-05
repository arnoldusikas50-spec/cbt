<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Jadwal Ujian' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Jadwal Ujian</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title text-bold"><i class="bi bi-calendar-event me-2"></i>Daftar Jadwal Ujian</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambah">
                            <i class="bi bi-calendar-plus me-1"></i> Buat Jadwal
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableData" class="table table-bordered table-striped table-hover table-sm w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50" class="text-center py-2">No</th>
                                    <th class="py-2">Mata Pelajaran</th>
                                    <th class="py-2">Bank Soal</th>
                                    <th class="py-2 text-center">Mulai</th>
                                    <th class="py-2 text-center">Selesai</th>
                                    <th class="py-2 text-center">Durasi</th>
                                    <th class="py-2 text-center">Status</th>
                                    <th width="100" class="text-center py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahLabel"><i class="bi bi-plus-circle me-2"></i>Buat Jadwal Ujian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Bank Soal <span class="text-danger">*</span></label>
                            <select name="id_bank" class="form-control select2" style="width:100%" required>
                                <option value="">-- Pilih Bank Soal --</option>
                                <?php foreach ($bank_soal ?? [] as $b): ?>
                                    <option value="<?= is_object($b) ? ($b->id_bank ?? '') : '' ?>">
                                        <?= is_object($b) ? esc($b->bank_kode ?? '') : '' ?> -
                                        <?= is_object($b) ? esc($b->bank_nama ?? '') : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Jenis Ujian</label>
                            <select name="id_jenis" class="form-select">
                                <?php foreach ($jenis_ujian ?? [] as $j): ?>
                                    <option value="<?= is_object($j) ? ($j->id_jenis ?? '') : '' ?>">
                                        <?= is_object($j) ? esc($j->nama_jenis ?? '') : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tanggal & Waktu Mulai</label>
                            <input type="datetime-local" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tanggal & Waktu Selesai</label>
                            <input type="datetime-local" name="tgl_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Durasi</label>
                            <div class="input-group">
                                <input type="number" name="durasi" class="form-control" value="60" min="1">
                                <span class="input-group-text">Menit</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">Acak Soal</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="acakSoal" name="acak_soal"
                                    value="1">
                                <label class="form-check-label" for="acakSoal">Aktifkan</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">Acak Jawaban</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="acakJawaban" name="acak_jawaban"
                                    value="1">
                                <label class="form-check-label" for="acakJawaban">Aktifkan</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-save me-1"></i>
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var table;

    $(document).ready(function () {
        ajaxcsrf();
        $('.select2').select2({
            theme: 'bootstrap-5'
        });

        table = $('#tableData').DataTable({
            processing: true,
            ajax: '<?= site_url('cbtjadwal/data') ?>',
            columns: [
                { data: 'no', class: 'text-center' },
                { data: 'nama_mapel' },
                { data: 'bank_nama' },
                { data: 'tgl_mulai', class: 'text-center' },
                { data: 'tgl_selesai', class: 'text-center' },
                { data: 'durasi', class: 'text-center', render: d => d + ' m' },
                {
                    data: 'status',
                    class: 'text-center',
                    render: function (d) {
                        return d == '1'
                            ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2">Aktif</span>'
                            : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Nonaktif</span>';
                    }
                },
                {
                    data: 'id_jadwal',
                    class: 'text-center',
                    render: function (data) {
                        return '<button class="btn btn-warning btn-xs shadow-sm me-1"><i class="bi bi-pencil"></i></button> ' +
                            '<button class="btn btn-danger btn-xs shadow-sm" onclick="hapus(' + data + ')"><i class="bi bi-trash"></i></button>';
                    }
                }
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });
    });

    $('#formTambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('cbtjadwal/save') ?>',
            type: 'POST',
            data: $(this).serialize() + '&' + CSRF_NAME + '=' + CSRF_HASH,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    showSuccessToast(res.message);
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    table.ajax.reload();
                } else {
                    showDangerToast(res.message);
                }
            }
        });
    });

    function hapus(id) {
        confirmDelete('<?= site_url('cbtjadwal/delete') ?>', { id_jadwal: id }, function () {
            table.ajax.reload();
        });
    }
</script>