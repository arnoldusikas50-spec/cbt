<div class="content-wrapper bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-header with-border">
                    <h3 class="card-title"><?= $subjudul ?></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalUpload">
                            <i class="fas fa-plus"></i> Upload Laporan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableLaporan" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Judul</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal Upload</th>
                                    <th>Uploader</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no=1; foreach($laporans as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= $row->jenis ?></td>
                                    <td><?= $row->judul ?></td>
                                    <td><?= $row->keterangan ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($row->created_at)) ?></td>
                                    <td><?= $row->first_name . ' ' . $row->last_name ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-info btn-preview" data-file="<?= $row->file ?>" data-id="<?= $row->id_laporan ?>"><i class="fas fa-eye"></i> Preview</button>
                                        <button class="btn btn-xs btn-danger btn-delete" data-id="<?= $row->id_laporan ?>"><i class="fas fa-trash"></i> Hapus</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="modalUpload" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Laporan (PDF)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open_multipart('laporan/upload', ['id' => 'formUpload']) ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Jenis Dokumen</label>
                    <select name="jenis" class="form-control" required>
                        <option value="">Pilih Jenis Dokumen</option>
                        <option value="Berita Acara">Berita Acara</option>
                        <option value="Pakta Integritas">Pakta Integritas</option>
                        <option value="Daftar Hadir Peserta">Daftar Hadir Peserta</option>
                        <option value="Laporan Pelaksanaan">Laporan Pelaksanaan</option>
                        <option value="Lain-Lain">Lain-Lain</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Judul Laporan</label>
                    <input type="text" name="judul" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>File Laporan (PDF)</label>
                    <div class="custom-file">
                        <input type="file" name="file_laporan" class="custom-file-input" id="customFile" accept="application/pdf" required>
                        <label class="custom-file-label" for="customFile">Pilih file</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="fas fa-save"></i> Simpan</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal Preview -->
<div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90% !important;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header">
                <h5 class="modal-title">Preview Laporan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 h-100">
                <iframe id="previewFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tableLaporan').DataTable();

    bsCustomFileInput.init();

    $('#formUpload').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#btnSimpan').attr('disabled', true).html('<i class="fas fa-spin fa-spinner"></i> Uploading...');
            },
            success: function(res) {
                if (res.status) {
                    Swal.fire('Sukses', res.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.msg, 'error');
                    $('#btnSimpan').attr('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                $('#btnSimpan').attr('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });

    $('.btn-preview').click(function() {
        var file = $(this).data('file');
        var url = '<?= base_url("uploads/laporan/") ?>' + file;
        $('#previewFrame').attr('src', url);
        $('#modalPreview').modal('show');
    });

    $('.btn-delete').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Laporan?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.value) {
                $.post('<?= base_url("laporan/delete") ?>', {id_laporan: id}, function(res) {
                    if (res.status) {
                        Swal.fire('Terhapus!', res.msg, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.msg, 'error');
                    }
                }, 'json');
            }
        })
    });
});
</script>
