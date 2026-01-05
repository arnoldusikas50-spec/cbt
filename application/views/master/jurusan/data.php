<main class="app-main bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4">
                <div class="card-header py-3">
                    <div class="card-title">
                        <h6><?= $subjudul ?></h6>
                    </div>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <!-- ikasmedia -->
                        <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </button>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createJurusanModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i> Tambah Jurusan
                        </button>
                        <button type="button" class="btn btn-tool ms-1" data-card-widget="collapse" title="Collapse">
                            <i class="bi bi-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php $totalJurusan = count($jurusans); ?>
                    <div class="mb-4">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Informasi Jurusan</h5>
                                <div class="text-muted">Kelola data jurusan dan mata pelajaran peminatan</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-primary px-3 py-2 text-sm">Total Jurusan:
                                    <?= $totalJurusan ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Panduan Pengelolaan
                        </h5>
                        <ul class="ps-3 mb-0">
                            <li>Gunakan tombol <span class="badge bg-primary">Tambah Data</span> untuk menambah jurusan
                                baru.</li>
                            <li>Checkbox di kolom pertama dapat dimanfaatkan untuk aksi massal seperti penghapusan.</li>
                            <li>Mapel peminatan ditampilkan dalam bentuk badge agar mudah dikenali.</li>
                            <li>Abaikan halaman ini jika sekolah tidak memiliki jurusan (jenjang SMP/MTs atau SD/MI).
                            </li>
                        </ul>
                    </div>

                    <?= form_open('', array('id' => 'bulk')) ?>
                    <div class="table-responsive">
                        <table id="jurusan"
                            class="w-100 table table-sm table-striped table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th height="40" class="text-center align-middle border-0" style="width: 40px">
                                        <input type="checkbox" id="select_all">
                                    </th>
                                    <th style="width: 40px" class="text-center align-middle border-0">No.</th>
                                    <th class="align-middle border-0">Kode</th>
                                    <th class="align-middle border-0">Jurusan</th>
                                    <th class="align-middle border-0">Mapel Peminatan</th>
                                    <th class="text-center align-middle border-0" style="width: 100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($jurusans as $row):
                                    $badges = '';
                                    foreach (explode(',', $row->mapel_peminatan ?? '') as $mid) {
                                        if ($mid != '' && isset($jurusan_mapels[$row->id_jurusan][$mid]))
                                            $badges .= '<span class="badge bg-info text-white shadow-xs me-1 mb-1">' . $jurusan_mapels[$row->id_jurusan][$mid] . '</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <div>
                                                <input id="check<?= $row->id_jurusan ?>" name="checked[]" class="check"
                                                    value="<?= $row->id_jurusan ?>" type="checkbox">
                                            </div>
                                        </td>
                                        <td class="text-center"><?= $no ?></td>
                                        <td><?= $row->kode_jurusan ?></td>
                                        <td><?= $row->nama_jurusan ?></td>
                                        <td><?= $badges ?></td>
                                        <td>
                                            <div class="text-center">
                                                <a class="btn btn-sm btn-info shadow-sm editRecord" data-bs-toggle="modal"
                                                    data-bs-target="#editJurusanModal"
                                                    data-deletable="<?= $row->deletable ?>"
                                                    data-mapel="<?= $row->mapel_peminatan ?>"
                                                    data-id='<?= $row->id_jurusan ?>' data-nama='<?= $row->nama_jurusan ?>'
                                                    data-kode='<?= $row->kode_jurusan ?>' title="Edit">
                                                    <i class="bi bi-pencil text-white"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php $no++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </section>
    </div>

    <?= form_open('create', array('id' => 'create')) ?>
    <div class="modal fade" id="createJurusanModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Jurusan*</label>
                        <div class="col-md-9">
                            <input type="text" id="createnama" name="nama_jurusan" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Kode*</label>
                        <div class="col-md-9">
                            <input type="text" id="createkode" name="kode_jurusan" class="form-control" required>
                        </div>
                    </div>

                    <?php foreach ($kode_peminatan as $kode): ?>
                        <div class="mb-3 row">
                            <?php if (isset($mapel_peminatan[$kode->kode_kel_mapel])): ?>
                                <label class="col-md-3 col-form-label"><?= $kode->nama_kel_mapel ?></label>
                                <div class="col-md-9">
                                    <?php if (count($mapel_peminatan) === 0): ?>
                                        <select class="form-control">
                                            <option value="0" selected disabled>Belum ada mapel <?= $kode->nama_kel_mapel ?>
                                            </option>
                                        </select>
                                    <?php else:
                                        foreach ($mapel_peminatan as $k_mpl => $mapels):
                                            if ($k_mpl === $kode->kode_kel_mapel): ?>
                                                <select name="mapel[]" id="create_mapel_peminatan<?= $kode->kode_kel_mapel ?>"
                                                    class="form-control mapel_peminatan select2" multiple="">
                                                    <?php foreach ($mapels as $kd_mpl => $mapel): ?>
                                                        <option class="opt-mapel" value="<?= $kd_mpl ?>"><?= $mapel ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php endif; endforeach; endif; ?>
                                </div>
                            <?php else: ?>
                                <label class="col-md-3 col-form-label">Mapel Peminatan</label>
                                <div class="col-md-9">
                                    <select class="form-control">
                                        <option value="0" selected disabled>Belum ada mapel peminatan</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
    <div class="modal fade" id="editJurusanModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row" id="formnama">
                        <label class="col-md-3 col-form-label">Jurusan*</label>
                        <div class="col-md-9">
                            <input type="text" id="namaEdit" name="nama_jurusan" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row" id="formkode">
                        <label class="col-md-3 col-form-label">Kode*</label>
                        <div class="col-md-9">
                            <input type="text" id="kodeEdit" name="kode_jurusan" class="form-control" required>
                        </div>
                    </div>
                    <?php
                    foreach ($kode_peminatan as $kode): ?>
                        <div class="mb-3 row">
                            <?php if (isset($mapel_peminatan[$kode->kode_kel_mapel])): ?>
                                <label class="col-md-3 col-form-label"><?= $kode->nama_kel_mapel ?></label>
                                <div class="col-md-9">
                                    <?php if (count($mapel_peminatan) === 0): ?>
                                        <select class="form-control">
                                            <option value="0" selected disabled>Belum ada mapel <?= $kode->nama_kel_mapel ?>
                                            </option>
                                        </select>
                                    <?php else:
                                        foreach ($mapel_peminatan as $k_mpl => $mapels):
                                            if ($k_mpl === $kode->kode_kel_mapel): ?>
                                                <select name="mapel[]" id="mapel_peminatan<?= $kode->kode_kel_mapel ?>"
                                                    class="form-control mapel_peminatan select2" multiple="">
                                                    <?php foreach ($mapels as $kd_mpl => $mapel): ?>
                                                        <option class="opt-mapel" value="<?= $kd_mpl ?>"><?= $mapel ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php endif; endforeach; endif; ?>
                                </div>

                            <?php else: ?>
                                <label class="col-md-3 col-form-label"><?= $kode->nama_kel_mapel ?></label>
                                <div class="col-md-9">
                                    <select class="form-control">
                                        <option value="0" selected disabled>Belum ada mapel peminatan</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdJurusan" name="id_jurusan" class="form-control">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <!--
<div class="template-demo d-flex justify-content-between flex-wrap">
    <button type="button" class="btn btn-success btn-fw" onclick="showSuccessToast()">Success</button>
    <button type="button" class="btn btn-info btn-fw" onclick="showInfoToast()">Info</button>
    <button type="button" class="btn btn-warning btn-fw" onclick="showWarningToast()">Warning</button>
    <button type="button" class="btn btn-danger btn-fw" onclick="showDangerToast()">Danger</button>
</div>
-->
    <script>
        var mapels = JSON.parse('<?= json_encode($mapel_peminatan) ?>');
    </script>
    <script src="<?= base_url() ?>/assets/app/js/master/jurusan/crud.js?v=<?= time() ?>"></script>