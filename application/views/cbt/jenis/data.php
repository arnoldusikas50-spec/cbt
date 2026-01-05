<?php

?>

<main class="app-main pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $judul ?></h6>
                    <div class="card-tools ms-auto">
                        <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </button>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createJenisModal"
                            class="btn btn-sm btn-primary"><i class="bi bi-plus"></i><span
                                class="d-none d-sm-inline-block ms-1">Tambah Jenis Ujian</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?= form_open('cbtjenis/delete', array('id' => 'bulk')); ?>
                    <div class="table-responsive">
                        <table id="jenis" class="w-100 table table-striped table-bordered table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="40" class="align-middle text-center p-0">
                                        <input class="select_all" type="checkbox">
                                    </th>
                                    <th width="40" class="align-middle text-center p-0">No.</th>
                                    <th>Jenis Ujian</th>
                                    <th>Kode Jenis</th>
                                    <th width="80" class="align-middle text-center">Status</th>
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
    <div class="modal fade" id="createJenisModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Jenis Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label for="nama_jenis" class="col-md-4 col-form-label">Jenis Ujian*</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="nama_jenis" placeholder="Nama Jenis" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="kode_jenis" class="col-md-4 col-form-label">Kode Jenis*</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="kode_jenis" placeholder="Kode Jenis Ujian"
                                required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                    <button type="reset" class="btn bg-warning text-white">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <?= form_open('update', array('id' => 'update')) ?>
    <div class="modal fade" id="editJenisModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Jenis Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Jenis Ujian*</label>
                        <div class="col-md-8">
                            <input type="text" id="namaEdit" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Kode Jenis*</label>
                        <div class="col-md-8">
                            <input type="text" id="kodeEdit" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdJenis" class="form-control">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <script src="<?= base_url() ?>/assets/app/js/cbt/jenis/crud.js"></script>