<?php

?>

<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $judul ?></h6>
                    <div class="card-tools ms-auto">
                        <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </button>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createRuangModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i> Tambah Ruang
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?= form_open('cbtruang/delete', array('id' => 'bulk')); ?>
                    <div class="table-responsive">
                        <table id="ruang" class="w-100 table table-striped table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th width="40" class="align-middle text-center border-0">
                                        <input class="select_all" type="checkbox">
                                    </th>
                                    <th width="40" class="align-middle text-center border-0">No.</th>
                                    <th class="border-0">Ruang Ujian</th>
                                    <th class="border-0">Kode Ruang</th>
                                    <th class="border-0">Total Sesi</th>
                                    <th class="align-middle text-center border-0" style="width: 100px">Aksi</th>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label for="nama_ruang" class="col-md-4 col-form-label">Ruang Ujian*</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="nama_ruang" placeholder="Nama Ruang" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="kode_ruang" class="col-md-4 col-form-label">Kode Ruang*</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="kode_ruang" placeholder="Kode Ruang Ujian"
                                required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="reset" class="btn btn-warning text-white">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Ruang Ujian*</label>
                        <div class="col-md-8">
                            <input type="text" id="namaEdit" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Kode Ruang*</label>
                        <div class="col-md-8">
                            <input type="text" id="kodeEdit" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdRuang" class="form-control">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <script src="<?= base_url() ?>/assets/app/js/cbt/ruang/crud.js"></script>