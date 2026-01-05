<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <?= esc($subjudul) ?>
                    </div>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <button type="button" onclick="location.reload()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </button>
                        <button type="button" data-from="add" data-bs-toggle="modal" data-bs-target="#createKelasModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i><span
                                class="d-none d-sm-inline-block text-nowrap">Tambah Kelas</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Kelola Data Kelas/Rombel</h5>
                                <div class="text-muted">Atur daftar kelas, tingkat, dan wali kelas untuk setiap rombongan belajar</div>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Panduan Pengaturan</h5>
                        <ul class="ps-3 mb-0">
                            <li>Gunakan tombol <span class="badge bg-primary">Tambah Kelas</span> untuk menambah kelas baru.</li>
                            <li>Setiap kelas harus memiliki wali kelas yang jelas untuk tanggung jawab akademik.</li>
                            <li>Tentukan jurusan dan tingkat kelas sesuai dengan struktur sekolah Anda.</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table id="tableKelas" class="table table-sm table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center align-middle" width="50">No.</th>
                                    <th class="text-center align-middle">Nama Kelas</th>
                                    <th class="text-center align-middle">Tingkat</th>
                                    <th class="text-center align-middle">Jurusan</th>
                                    <th class="text-center align-middle">Wali Kelas</th>
                                    <th class="text-center align-middle">Jumlah Siswa</th>
                                    <th class="text-center align-middle" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="modal fade" id="createKelasModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Nama Kelas*</label>
                        <div class="col-md-9">
                            <input type="text" id="createNama" name="nama_kelas" class="form-control" required
                                placeholder="Contoh: X IPA 1">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Kode Kelas</label>
                        <div class="col-md-9">
                            <input type="text" id="createKode" name="kode_kelas" class="form-control"
                                placeholder="Contoh: 10-IPA-1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tingkat*</label>
                                <select id="createLevel" name="level" class="form-select" required>
                                    <option value="">-- Pilih Tingkat --</option>
                                    <option value="10">Kelas 10</option>
                                    <option value="11">Kelas 11</option>
                                    <option value="12">Kelas 12</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jurusan*</label>
                                <select id="createJurusan" name="id_jurusan" class="form-select" required>
                                    <option value="">-- Pilih Jurusan --</option>
                                    <?php if (isset($jurusans) && is_array($jurusans)): ?>
                                        <?php foreach ($jurusans as $j): ?>
                                            <option value="<?= $j->id_jurusan ?>">
                                                <?= esc($j->nama_jurusan ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wali Kelas*</label>
                        <select id="createWali" name="wali_kelas" class="form-select" required>
                            <option value="">-- Pilih Wali Kelas --</option>
                            <?php if (isset($gurus) && is_array($gurus)): ?>
                                <?php foreach ($gurus as $g): ?>
                                    <option value="<?= $g->id_guru ?>">
                                        <?= esc($g->nama_guru ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdKelas" name="id_kelas">
                    <input type="hidden" id="method" name="method">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="submit-kelas">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var table;
    $(document).ready(function () {
        table = $('#tableKelas').DataTable({
            ajax: '<?= site_url('datakelas/data') ?>',
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'nama_kelas' },
                { data: 'level', className: 'text-center' },
                { data: 'jurusan', className: 'text-center' },
                { data: 'wali_kelas', className: 'text-center' },
                {
                    data: 'jumlah',
                    className: 'text-center',
                    render: function (data) {
                        return '<span class="badge bg-info">' + data + ' siswa</span>';
                    }
                },
                {
                    data: 'id_kelas',
                    className: 'text-center',
                    render: function (data) {
                        return `
                            <button type="button" class="btn btn-sm btn-info text-white btn-edit shadow-sm" 
                                data-id="${data}" data-bs-toggle="modal" data-bs-target="#createKelasModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-hapus shadow-sm" data-id="${data}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });

        $('#tableKelas').on('click', '.btn-edit', function (e) {
            let id = $(this).data('id');
            let row = table.row((idx, data) => data.id_kelas === id).data();
            
            if (row) {
                $('#createModalLabel').text('Edit Kelas');
                $('#editIdKelas').val(row.id_kelas);
                $('#createNama').val(row.nama_kelas);
                $('#createKode').val(row.kode_kelas);
                $('#createLevel').val(row.level);
                $('#createJurusan').val(row.id_jurusan);
                $('#createWali').val(row.wali_kelas);
                $('#submit-kelas').html('<i class="bi bi-save me-1"></i> Update');
                $('#method').val('edit');
            }
        });

        $('#createKelasModal').on('show.bs.modal', function (e) {
            // Reset form for new entry if not clicked from edit button
            if (!$('#editIdKelas').val()) {
                $('#createModalLabel').text('Tambah Kelas');
                $('#createForm')[0].reset();
                $('#submit-kelas').html('<i class="bi bi-save me-1"></i> Simpan');
                $('#method').val('add');
            }
        });

        $('#createKelasModal').on('hidden.bs.modal', function (e) {
            $('#createForm')[0].reset();
            $('#editIdKelas').val('');
            $('#method').val('');
        });

        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            let url = $('#editIdKelas').val() ? '<?= site_url('datakelas/update') ?>' : '<?= site_url('datakelas/save') ?>';
            
            $.post(url, $(this).serialize(), function (res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message || res.msg, 'success').then(() => {
                        $('#createKelasModal').modal('hide');
                        table.ajax.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message || res.msg, 'error');
                }
            });
        });

        $('#tableKelas').on('click', '.btn-hapus', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Kelas?',
                text: 'Data yang berhubungan akan ikut terpengaruh.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= site_url('datakelas/delete') ?>', { id_kelas: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.message || res.msg, res.status ? 'success' : 'error')
                            .then(() => { if (res.status) table.ajax.reload(); });
                    });
                }
            });
        });
    });
</script>