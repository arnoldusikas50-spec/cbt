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
                        <button type="button" data-toggle="modal" data-target="#createRuangModal"
                                class="btn btn-sm btn-primary"><i
                                    class="fas fa-plus"></i><span class="d-none d-sm-inline-block ml-1">Tambah Ruang Ujian</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php $totalRuang = isset($total_ruang) ? $total_ruang : '-'; ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Pengelolaan Ruang Ujian</h5>
                                <div class="text-muted">Kelompokkan peserta berdasarkan ruang ujian dan kode ruang</div>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <span class="badge badge-primary px-3 py-2 text-sm">Total Ruang: <?= $totalRuang ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengelolaan</h5>
                        <ul class="pl-3 mb-0">
                            <li>Gunakan tombol <span class="badge badge-primary">Tambah Ruang Ujian</span> untuk membuat kelompok ruang baru.</li>
                            <li>Kode ruang wajib unik dan dipakai pada distribusi peserta dan pengawas.</li>
                            <li>Jumlah total sesi dihitung otomatis dari keterkaitan ruang dengan jadwal ujian.</li>
                        </ul>
                    </div>

                    <?= form_open('cbtruang/delete', array('id' => 'bulk')); ?>
                    <div class="table-responsive">
                        <table id="ruang" class="w-100 table table-striped table-bordered table-hover">
                            <thead>
                            <tr class="alert alert-default-primary">
                                <th width="40" class="align-middle text-center p-0">
                                    <input class="select_all" type="checkbox">
                                </th>
                                <th width="40" class="align-middle text-center p-0">No.</th>
                                <th>Kelompok/Ruang Ujian</th>
                                <th>Kode Ruang</th>
                                <th>Jumlah Total Sesi</th>
                                <th width="100" class="align-middle text-center p-0">Aksi</th>
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
<div class="modal fade" id="createRuangModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Tambah Ruang Ujian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label for="nama_ruang" class="col-md-4 col-form-label">Ruang Ujian*</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="nama_ruang" placeholder="Nama Ruang" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="kode_ruang" class="col-md-4 col-form-label">Kode Ruang*</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="kode_ruang" placeholder="Kode Ruang Ujian"
                               required>
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
<div class="modal fade" id="editRuangModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Ruang Ujian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Ruang Ujian*</label>
                    <div class="col-md-8">
                        <input type="text" id="namaEdit" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Kode Ruang*</label>
                    <div class="col-md-8">
                        <input type="text" id="kodeEdit" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="editIdRuang" class="form-control">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<script src="<?= base_url() ?>/assets/app/js/cbt/ruang/crud.js"></script>


