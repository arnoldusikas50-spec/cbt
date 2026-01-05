<?php /* Cache bust: 20260103-1156 */ ?>
<main class="app-main bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-4 position-relative">
                            <div class="d-flex align-items-center position-relative z-1">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                                    <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Ringkasan Arsip Laporan</h5>
                                    <p class="text-muted mb-0 small">Pantau kelengkapan dokumen administrasi ujian
                                        secara real-time</p>
                                </div>
                                <div class="ms-auto d-none d-md-flex gap-2">
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary border-opacity-10">
                                        <i class="bi bi-database me-1"></i> Total: <?= $summary['total'] ?>
                                    </span>
                                    <span
                                        class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill border border-info border-opacity-10">
                                        <i class="bi bi-file-earmark-richtext me-1"></i> Berita Acara:
                                        <?= $summary['jenis']['Berita Acara'] ?? 0 ?>
                                    </span>
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill border border-success border-opacity-10">
                                        <i class="bi bi-shield-check me-1"></i> Pakta:
                                        <?= $summary['jenis']['Pakta Integritas'] ?? 0 ?>
                                    </span>
                                </div>
                            </div>
                            <!-- Mobile statistics -->
                            <div class="d-flex d-md-none flex-wrap gap-2 mt-3 z-1 position-relative">
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary border-opacity-10">
                                    <i class="bi bi-database me-1"></i> Total: <?= $summary['total'] ?>
                                </span>
                                <!-- Add others if needed for mobile -->
                            </div>
                            <!-- Background decoration -->
                            <div class="position-absolute top-0 end-0 h-100 w-25 bg-gradient-light opacity-25"
                                style="transform: skewX(-20deg);"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header with-border">
                    <h3 class="card-title"><?= $subjudul ?></h3>
                    <div class="card-tools ms-auto">
                        <div class="btn-group shadow-sm">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-pencil me-1"></i> Buat Dokumen
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item btn-upload-type" href="#" data-jenis="Berita Acara"><i
                                            class="bi bi-file-text me-2"></i>Berita Acara</a></li>
                                <li>
                                    <?php if ($has_pakta_integritas): ?>
                                        <a class="dropdown-item disabled" href="#"
                                            style="cursor: not-allowed; opacity: 0.5;" data-bs-toggle="tooltip"
                                            title="Anda sudah memiliki Pakta Integritas">
                                            <i class="bi bi-shield-check me-2"></i>Pakta Integritas
                                            <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Sudah
                                                Ada</span>
                                        </a>
                                    <?php else: ?>
                                        <a class="dropdown-item btn-upload-type" href="#" data-jenis="Pakta Integritas">
                                            <i class="bi bi-shield-check me-2"></i>Pakta Integritas
                                        </a>
                                    <?php endif; ?>
                                </li>
                                <li><a class="dropdown-item btn-upload-type" href="#"
                                        data-jenis="Daftar Hadir Peserta"><i class="bi bi-list-check me-2"></i>Daftar
                                        Hadir</a></li>
                                <li><a class="dropdown-item btn-upload-type" href="#"
                                        data-jenis="Laporan Pelaksanaan"><i class="bi bi-journal-text me-2"></i>Laporan
                                        Pelaksanaan</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item btn-upload-type" href="#" data-jenis="Lain-Lain"><i
                                            class="bi bi-archive me-2"></i>Dokumen Lain</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jenis Dokumen</label>
                            <select id="filterJenis" class="form-select form-select-sm shadow-sm">
                                <option value="">Semua Dokumen</option>
                                <option value="Berita Acara">Berita Acara</option>
                                <option value="Pakta Integritas">Pakta Integritas</option>
                                <option value="Daftar Hadir Peserta">Daftar Hadir Peserta</option>
                                <option value="Laporan Pelaksanaan">Laporan Pelaksanaan</option>
                                <option value="Lain-Lain">Lain-Lain</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Pengawas</label>
                            <select id="filterUploader" class="form-select form-select-sm shadow-sm">
                                <option value="">Semua Pengawas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status Dokumen</label>
                            <select id="filterStatus" class="form-select form-select-sm shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="Checking">Checking</option>
                                <option value="Disetujui">Disetujui</option>
                                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tableLaporan" class="table table-striped table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th width="20" class="text-center align-middle">No</th>
                                    <th class="align-middle">Judul</th>
                                    <th class="align-middle">Keterangan</th>
                                    <th class="align-middle">Tanggal Upload</th>
                                    <th class="align-middle">Uploader</th>
                                    <th class="align-middle text-center">Status</th>
                                    <th width="120" class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($laporans as $row):
                                    $stt = $row->status;
                                    $status_badge = '<span class="badge bg-warning">Checking</span>';
                                    if ($stt == 1) {
                                        $title = 'Disetujui oleh ' . ($row->approver_first ?? '') . ' pada ' . date('d-m-Y H:i', strtotime($row->tgl_approval));
                                        $status_badge = '<span class="badge bg-success" data-bs-toggle="tooltip" title="' . $title . '">Disetujui</span>';
                                    } elseif ($stt == 2) {
                                        $title = 'Alasan: ' . $row->alasan_correction;
                                        $status_badge = '<span class="badge bg-danger" data-bs-toggle="tooltip" title="' . $title . '">Perlu Perbaikan</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= $row->judul ?><br>
                                            <badge class="badge bg-success"><?= $row->jenis ?></badge>
                                        </td>
                                        <td><?= $row->keterangan ?>
                                        </td>
                                        <td><?= date('d-m-Y H:i', strtotime($row->created_at)) ?></td>
                                        <td><?= $row->first_name . ' ' . $row->last_name ?></td>
                                        <td class="text-center"><?= $status_badge ?></td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn btn-sm btn-info shadow-sm btn-preview"
                                                    data-file="<?= $row->file ?>" data-id="<?= $row->id_laporan ?>"
                                                    data-jenis="<?= $row->jenis ?>" data-bs-toggle="tooltip"
                                                    title="Preview">
                                                    <i class="bi bi-eye text-white"></i>
                                                </button>
                                                <?php if ($stt != 1 && ($row->jenis == 'Berita Acara' || $row->jenis == 'Pakta Integritas')): ?>
                                                    <?php
                                                    // Check if user can edit (owner or admin/panitia non-pengawas)
                                                    $can_edit = false;
                                                    if ($is_admin || (!$is_pengawas && isset($panitia) && $panitia->jabatan != 'Pengawas')) {
                                                        $can_edit = true;
                                                    } elseif ($row->id_user == $user->id) {
                                                        $can_edit = true;
                                                    }
                                                    if ($can_edit): ?>
                                                        <button class="btn btn-sm btn-secondary shadow-sm btn-edit"
                                                            data-id="<?= $row->id_laporan ?>" data-jenis="<?= $row->jenis ?>"
                                                            data-bs-toggle="tooltip" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php if (!$is_pengawas && ($is_admin || ($panitia && $panitia->jabatan != 'Pengawas'))): ?>
                                                    <?php if ($stt != 1): ?>
                                                        <button class="btn btn-sm btn-success shadow-sm btn-approve"
                                                            data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip"
                                                            title="Approve">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning shadow-sm btn-reject"
                                                            data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip"
                                                            title="Tolak">
                                                            <i class="bi bi-x text-white"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-danger shadow-sm btn-delete"
                                                        data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip"
                                                        title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <?php if ($stt != 1): ?>
                                                        <button class="btn btn-sm btn-danger shadow-sm btn-delete"
                                                            data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip"
                                                            title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($stt == 2): ?>
                                                        <button class="btn btn-sm btn-primary shadow-sm btn-reason"
                                                            data-reason="<?= $row->alasan_correction ?>" data-bs-toggle="tooltip"
                                                            title="Lihat Alasan">
                                                            <i class="bi bi-info-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
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

    <!-- Modal Berita Acara -->
    <div class="modal fade" id="modalBeritaAcara" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalBeritaAcaraTitle"><i class="bi bi-file-text me-2"></i>Form Berita
                        Acara</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <?= form_open_multipart('laporan/save_berita_acara', ['id' => 'formBeritaAcara']) ?>
                <input type="hidden" name="id_laporan" id="editBeritaAcaraId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mata Pelajaran</label>
                            <input type="hidden" name="mapel_nama" id="inputMapelNama">
                            <select name="mapel" id="selectMapel" class="form-select select2" style="width: 100%;"
                                required>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php
                                // Fallback: get mapel directly if jadwal_list empty
                                $mapel_options = isset($jadwal_list) && !empty($jadwal_list)
                                    ? $jadwal_list
                                    : get_instance()->db->order_by('nama_mapel', 'ASC')->get('master_mapel')->result();
                                foreach ($mapel_options as $j): ?>
                                    <option value="<?= $j->id_mapel ?>" data-nama="<?= $j->nama_mapel ?>">
                                        <?= $j->nama_mapel ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Ujian</label>
                            <input type="date" name="waktu_ujian" class="form-control" required
                                value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai</label>
                            <input type="text" name="jam_mulai" class="form-control timepicker" placeholder="HH:mm"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai</label>
                            <input type="text" name="jam_selesai" class="form-control timepicker" placeholder="HH:mm"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ruang</label>
                            <input type="hidden" name="ruang_nama" id="inputRuangNama">
                            <select name="ruang" id="selectRuang" class="form-select" required>
                                <option value="">Pilih Ruang</option>
                                <?php
                                $ruang_options = isset($ruang_list) && !empty($ruang_list)
                                    ? $ruang_list
                                    : get_instance()->db->get('cbt_ruang')->result();
                                foreach ($ruang_options as $r): ?>
                                    <option value="<?= $r->id_ruang ?>" data-nama="<?= $r->nama_ruang ?>">
                                        <?= $r->nama_ruang ?>     <?= isset($r->kode_ruang) ? '(' . $r->kode_ruang . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sesi</label>
                            <input type="hidden" name="sesi_nama" id="inputSesiNama">
                            <select name="sesi" id="selectSesi" class="form-select" required>
                                <option value="">Pilih Sesi</option>
                                <?php
                                $sesi_options = isset($sesi_list) && !empty($sesi_list)
                                    ? $sesi_list
                                    : get_instance()->db->get('cbt_sesi')->result();
                                foreach ($sesi_options as $s): ?>
                                    <option value="<?= $s->id_sesi ?>" data-nama="<?= $s->nama_sesi ?>">
                                        <?= $s->nama_sesi ?>
                                        <?= isset($s->waktu_mulai) ? '(' . $s->waktu_mulai . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body py-2">
                            <label class="form-label fw-bold mb-2">Jumlah Peserta</label>
                            <div class="row">
                                <div class="col-4">
                                    <small class="d-block text-muted">Seharusnya</small>
                                    <input type="number" name="jml_seharusnya" class="form-control form-control-sm"
                                        required min="0">
                                </div>
                                <div class="col-4">
                                    <small class="d-block text-muted">Hadir</small>
                                    <input type="number" name="jml_hadir" class="form-control form-control-sm" required
                                        min="0">
                                </div>
                                <div class="col-4">
                                    <small class="d-block text-muted">Tidak Hadir</small>
                                    <input type="number" name="jml_tidak_hadir" class="form-control form-control-sm"
                                        required min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Peserta Tidak Hadir</label>
                        <select name="siswa_tidak_hadir[]" id="selectSiswaTidakHadir" class="form-select select2-siswa"
                            multiple="multiple" data-placeholder="Cari dan pilih siswa yang tidak hadir..."
                            style="width: 100%;">
                            <?php if (isset($siswa_list)):
                                foreach ($siswa_list as $s): ?>
                                    <option value="<?= $s->id_siswa ?>" data-nama="<?= $s->nama ?>"
                                        data-no="<?= $s->username ?>">
                                        <?= $s->nama ?> - <?= $s->username ?>
                                    </option>
                                <?php endforeach; endif; ?>
                        </select>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Uraian Pelaksanaan</label>
                        <textarea name="uraian" class="form-control" rows="3"
                            placeholder="Contoh: Pada hari ini telah dilaksanakan ujian dengan kondisi berjalan tertib..."
                            required></textarea>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kendala / Kejadian Khusus <small class="text-muted">(Kosongkan jika
                                tidak ada kendala)</small></label>
                        <textarea name="kendala" class="form-control" rows="2"
                            placeholder="Contoh: Peserta terlambat, Komputer error, Listrik padam, Jaringan internet terputus, dsb."></textarea>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tindak Lanjut / Solusi <small class="text-muted">(Kosongkan jika tidak
                                ada kendala)</small></label>
                        <textarea name="solusi" class="form-control" rows="2"
                            placeholder="Contoh: Restart komputer, Penggantian perangkat, Penambahan waktu, dsb."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Dokumentasi (Max 3)</label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="file" name="foto_1" id="inputFoto1"
                                    class="form-control form-control-sm foto-input" accept="image/*" required
                                    data-preview="previewFoto1">
                                <small class="text-xs text-muted">Foto 1 *</small>
                                <div id="previewFoto1" class="mt-2 text-center" style="display:none;">
                                    <img src="" class="img-thumbnail" style="max-height: 120px; max-width: 100%;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="file" name="foto_2" id="inputFoto2"
                                    class="form-control form-control-sm foto-input" accept="image/*"
                                    data-preview="previewFoto2">
                                <small class="text-xs text-muted">Foto 2</small>
                                <div id="previewFoto2" class="mt-2 text-center" style="display:none;">
                                    <img src="" class="img-thumbnail" style="max-height: 120px; max-width: 100%;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="file" name="foto_3" id="inputFoto3"
                                    class="form-control form-control-sm foto-input" accept="image/*"
                                    data-preview="previewFoto3">
                                <small class="text-xs text-muted">Foto 3</small>
                                <div id="previewFoto3" class="mt-2 text-center" style="display:none;">
                                    <img src="" class="img-thumbnail" style="max-height: 120px; max-width: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanBerita"><i class="bi bi-save me-1"></i>
                        <span class="btn-text">Simpan Berita Acara</span></button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <!-- Modal Pakta Integritas -->
    <div class="modal fade" id="modalPaktaIntegritas" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalPaktaIntegritasTitle"><i class="bi bi-shield-check me-2"></i>Form
                        Pakta Integritas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <?= form_open('laporan/save_pakta_integritas', ['id' => 'formPaktaIntegritas']) ?>
                <input type="hidden" name="id_laporan" id="editPaktaIntegritasId">
                <div class="modal-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-person me-1"></i> Data Pribadi</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required
                                value="<?= isset($panitia->nama_lengkap) ? $panitia->nama_lengkap : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP/NIK</label>
                            <input type="text" name="nip" class="form-control"
                                value="<?= isset($panitia->nip) ? $panitia->nip : '' ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tugas/Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control" required
                                value="<?= isset($panitia->jabatan) ? $panitia->jabatan : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asal Instansi <span class="text-danger">*</span></label>
                            <input type="text" name="instansi" class="form-control" required
                                value="<?= isset($setting->nama_sekolah) ? $setting->nama_sekolah : '' ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Handphone</label>
                            <input type="text" name="no_hp" class="form-control"
                                value="<?= isset($panitia->no_hp) ? $panitia->no_hp : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= isset($user->email) ? $user->email : '' ?>">
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-geo-alt me-1"></i> Tempat Bertugas</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control"
                                value="<?= isset($setting->provinsi) ? $setting->provinsi : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kabupaten/Kota</label>
                            <input type="text" name="kota" class="form-control"
                                value="<?= isset($setting->kota) ? $setting->kota : '' ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tempat Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="text" name="tempat" class="form-control" required
                                value="<?= isset($setting->nama_sekolah) ? $setting->nama_sekolah : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="text" name="tanggal_pelaksanaan" class="form-control" required
                                placeholder="Contoh: 10 s/d 14 Januari 2026">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSimpanPakta"><i class="bi bi-save me-1"></i>
                        <span class="btn-text">Simpan Pakta Integritas</span></button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <!-- Modal Upload -->
    <div class="modal fade" id="modalUpload" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalUploadTitle">Upload Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <?= form_open_multipart('laporan/upload', ['id' => 'formUpload']) ?>
                <div class="modal-body">
                    <input type="hidden" name="jenis" id="inputJenis">
                    <div class="mb-3">
                        <label>Judul Laporan</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>File Laporan (PDF)</label>
                        <div class="custom-file">
                            <input type="file" name="file_laporan" class="custom-file-input" id="customFile"
                                accept="application/pdf" required>
                            <label class="custom-file-label" for="customFile">Pilih file</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan"><i class="bi bi-save"></i>
                        Simpan</button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body p-0 h-100">
                    <iframe id="previewFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reject -->
    <div class="modal fade" id="modalReject" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <?= form_open('laporan/reject', ['id' => 'formReject']) ?>
                <div class="modal-body">
                    <input type="hidden" name="id_laporan" id="rejectId">
                    <div class="mb-3">
                        <label>Alasan Perbaikan</label>
                        <textarea name="alasan" class="form-control" required rows="3"
                            placeholder="Tuliskan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Kirim Revisi</button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer" style="z-index: 9999;"></div>

    <!-- Manual CSRF Token -->
    <input type="hidden" id="csrfToken" name="<?= $this->security->get_csrf_token_name() ?>"
        value="<?= $this->security->get_csrf_hash() ?>">

    <!-- Custom CSS for SweetAlert Modal -->
    <style>
        /* Modal Size */
        .swal-wide {
            width: 650px !important;
            max-width: 95% !important;
        }

        .swal-wide .swal2-html-container {
            text-align: left !important;
            overflow: visible !important;
        }

        .swal-html-custom {
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Modal Animation */
        .animated-modal {
            animation: modalSlideIn 0.4s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Custom Confirm Button */
        .swal-confirm-custom {
            font-size: 1rem !important;
            padding: 12px 32px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4) !important;
            transition: all 0.3s ease !important;
        }

        .swal-confirm-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important;
        }

        /* Custom Cancel Button */
        .swal-cancel-custom {
            font-size: 0.9rem !important;
            padding: 10px 24px !important;
            border-radius: 8px !important;
            opacity: 0.8 !important;
            transition: all 0.3s ease !important;
        }

        .swal-cancel-custom:hover {
            opacity: 1 !important;
        }

        /* Icon Animation */
        .swal2-icon.swal2-info {
            border-color: #10b981 !important;
            color: #10b981 !important;
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }
    </style>

    <script>
        $(document).ready(function () {
            // --- Bootstrap Alert Helper ---
            // --- Bootstrap Toast Helper ---
            function showBootstrapAlert(type, message) {
                const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
                const bgClass = type === 'success' ? 'bg-success text-white' : 'bg-danger text-white';
                const toastId = 'toast-' + Date.now();

                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi ${icon} me-2"></i> ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>`;

                $('#toastContainer').append(toastHtml);
                var toastEl = document.getElementById(toastId);
                var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                toast.show();

                // Cleanup after hide
                toastEl.addEventListener('hidden.bs.toast', function () {
                    $(this).remove();
                });
            }

            $('[data-bs-toggle="tooltip"]').tooltip();
            var table = $('#tableLaporan').DataTable({
                "pageLength": 10,
                "order": [[4, "desc"]], // Default sort by upload date
                "columnDefs": [{
                    "searchable": false,
                    "orderable": false,
                    "targets": 0
                }]
            });

            // Auto-numbering for the first column
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Populate Uploader Filter from Table Data (Column 5)
            table.column(5).data().unique().sort().each(function (d, j) {
                var text = $('<div>').html(d).text();
                if (text) {
                    $('#filterUploader').append('<option value="' + text + '">' + text + '</option>');
                }
            });

            // Filter Event Listeners
            $('#filterJenis').on('change', function () {
                table.column(1).search(this.value).draw();
            });

            $('#filterUploader').on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                table.column(5).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            $('#filterStatus').on('change', function () {
                table.column(6).search(this.value).draw();
            });

            bsCustomFileInput.init();

            // =====================================================
            // Auto-Show Reminder for Pengawas without Pakta Integritas
            // =====================================================
            <?php if ($is_pengawas && !$has_pakta_integritas): ?>
                // Show reminder after short delay to ensure page is fully loaded
                setTimeout(function () {
                    Swal.fire({
                        icon: 'info',
                        title: 'Reminder: Pakta Integritas',
                        text: 'Anda belum membuat Pakta Integritas. Setiap pengawas diwajibkan membuat Pakta Integritas sebagai komitmen dalam pelaksanaan ujian.',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-shield-check me-1"></i> Buat Sekarang',
                        cancelButtonText: 'Nanti Saja',
                        allowOutsideClick: false,
                        reverseButtons: true
                    }).then((result) => {
                        // Handle dismiss (Nanti Saja button)
                        if (result.dismiss) {
                            return;
                        }

                        // Handle confirm (Buat Sekarang button)
                        if (result.value === true) {
                            // Close SweetAlert
                            Swal.close();

                            // Wait for close animation
                            setTimeout(function () {
                                // Try to open modal
                                var modalEl = document.getElementById('modalPaktaIntegritas');
                                if (modalEl) {
                                    try {
                                        var paktaModal = new bootstrap.Modal(modalEl);
                                        paktaModal.show();
                                    } catch (e) {
                                        $('#modalPaktaIntegritas').modal('show');
                                    }
                                } else {
                                    // Modal not found - show helpful message
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Informasi',
                                        text: 'Silakan klik tombol "Buat Dokumen" lalu pilih "Pakta Integritas" untuk membuat dokumen.',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            }, 500);
                        }
                    });
                }, 800); // Delay 800ms
            <?php endif; ?>

            $('#formUpload').submit(function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('#btnSimpan').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');
                    },
                    success: function (res) {
                        if (res.status) {
                            showBootstrapAlert('success', res.msg);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showBootstrapAlert('danger', res.msg);
                            $('#btnSimpan').attr('disabled', false).html('<i class="bi bi-save"></i> Simpan');
                        }
                    },
                    error: function () {
                        showBootstrapAlert('danger', 'Terjadi kesalahan server');
                        $('#btnSimpan').attr('disabled', false).html('<i class="bi bi-save"></i> Simpan');
                    }
                });
            });

            $('#formReject').submit(function (e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.post($(this).attr('action'), formData, function (res) {
                    if (res.status) {
                        showBootstrapAlert('success', res.msg);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showBootstrapAlert('danger', res.msg);
                    }
                }, 'json').fail(function () {
                    showBootstrapAlert('danger', 'Terjadi kesalahan server');
                });
            });

            // Init Timepicker
            if ($.fn.datetimepicker) {
                $('.timepicker').datetimepicker({
                    datepicker: false,
                    format: 'H:i',
                    step: 15
                });
            } else {
                console.warn("datetimepicker library not loaded");
            }

            $('#tableLaporan').on('click', '.btn-preview', function () {
                var file = $(this).data('file');
                var jenis = $(this).data('jenis');
                var id = $(this).data('id');

                if (jenis === 'Berita Acara') {
                    var url = '<?= base_url("laporan/cetak_berita_acara/") ?>' + id;
                    $('#previewFrame').attr('src', url);
                    $('#modalPreview').modal('show');
                    return;
                }

                if (jenis === 'Pakta Integritas') {
                    var url = '<?= base_url("laporan/cetak_pakta_integritas/") ?>' + id;
                    $('#previewFrame').attr('src', url);
                    $('#modalPreview').modal('show');
                    return;
                }

                var url = '<?= base_url("uploads/laporan/") ?>' + file;
                $('#previewFrame').attr('src', url);
                $('#modalPreview').modal('show');
            });

            $('#tableLaporan').on('click', '.btn-delete', function () {
                var id = $(this).data('id');
                console.log('Delete button clicked, ID:', id);
                var $row = $(this).closest('tr'); // Get the row

                var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
                var csrfHash = $('#csrfToken').val(); // Ambil dari input manual

                console.log('CSRF Name:', csrfName, 'CSRF Hash:', csrfHash);

                if (!csrfHash) {
                    console.error('CSRF token is empty!');
                    showBootstrapAlert('danger', 'Token keamanan kadaluarsa, silakan refresh halaman.');
                    return;
                }

                Swal.fire({
                    title: 'Hapus Laporan?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => {
                        console.log('User confirmed delete - preConfirm triggered');
                        var data = { id_laporan: id };
                        data[csrfName] = csrfHash;

                        console.log('Sending delete request with data:', data);
                        console.log('URL:', '<?= base_url("laporan/delete") ?>');

                        return $.ajax({
                            url: '<?= base_url("laporan/delete") ?>',
                            type: 'POST',
                            data: data,
                            dataType: 'json'
                        }).then(response => {
                            console.log('Delete response received:', response);
                            if (!response.status) {
                                throw new Error(response.msg || 'Gagal menghapus');
                            }
                            return response;
                        }).catch(error => {
                            console.error('Delete request failed:', error);
                            if (error.responseJSON) {
                                console.error('Response JSON:', error.responseJSON);
                                Swal.showValidationMessage(`Gagal: ${error.responseJSON.msg || error.statusText}`);
                            } else if (error.responseText) {
                                console.error('Response Text:', error.responseText);
                                Swal.showValidationMessage(`Server Error: ${error.statusText}`);
                            } else {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            }
                        });
                    }
                }).then((result) => {
                    console.log('==================== SWAL THEN CALLBACK ====================');
                    console.log('Full result:', JSON.stringify(result, null, 2));
                    console.log('result.isConfirmed:', result.isConfirmed);
                    console.log('result.value:', result.value);
                    console.log('result.isDismissed:', result.isDismissed);

                    // Check if user cancelled
                    if (result.isDismissed) {
                        console.log('? DISMISSED - User cancelled');
                        return;
                    }

                    // Check if we have a value (from preConfirm Promise resolution)
                    if (result.value && result.value.status) {
                        console.log('? DELETE SUCCESS - Will process and reload');

                        // Update CSRF Token
                        try {
                            if (result.value.csrf_token) {
                                $('#csrfToken').val(result.value.csrf_token);
                                $('input[name="' + csrfName + '"]').val(result.value.csrf_token);
                                console.log('? CSRF token updated');
                            }
                        } catch (e) {
                            console.error('Error updating CSRF:', e);
                        }

                        // Show notification
                        try {
                            console.log('Showing toast:', result.value.msg);
                            showBootstrapAlert('success', result.value.msg);
                            console.log('? Toast shown');
                        } catch (e) {
                            console.error('Error showing toast:', e);
                        }

                        // RELOAD
                        console.log('? Setting up reload in 1 second...');
                        setTimeout(function () {
                            console.log('?? RELOADING NOW!');
                            window.location.reload(true);
                        }, 1000);
                    } else {
                        console.warn('? NO VALUE or FAILED - result.value:', result.value);
                    }
                    console.log('==================== END CALLBACK ====================');
                }).catch(function (error) {
                    console.error('FATAL ERROR in .then() callback:', error);
                });
            });

            $('#tableLaporan').on('click', '.btn-approve', function () {
                var id = $(this).data('id');
                console.log('Approve button clicked, ID:', id);

                var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
                var csrfHash = $('#csrfToken').val();

                console.log('CSRF Name:', csrfName, 'CSRF Hash:', csrfHash);

                if (!csrfHash) {
                    console.error('CSRF token is empty!');
                    showBootstrapAlert('danger', 'Token keamanan kadaluarsa, silakan refresh halaman.');
                    return;
                }

                Swal.fire({
                    title: 'Setujui Dokumen?',
                    text: "Dokumen yang disetujui tidak dapat dihapus oleh pengawas!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => {
                        console.log('User confirmed approve - preConfirm triggered');
                        var data = { id_laporan: id };
                        data[csrfName] = csrfHash;

                        console.log('Sending approve request with data:', data);
                        console.log('URL:', '<?= base_url("laporan/approve") ?>');

                        return $.ajax({
                            url: '<?= base_url("laporan/approve") ?>',
                            type: 'POST',
                            data: data,
                            dataType: 'json'
                        }).then(response => {
                            console.log('Approve response received:', response);
                            if (!response.status) {
                                throw new Error(response.msg || 'Gagal menyetujui');
                            }
                            return response;
                        }).catch(error => {
                            console.error('Approve request failed:', error);
                            if (error.responseJSON) {
                                console.error('Response JSON:', error.responseJSON);
                                Swal.showValidationMessage(`Gagal: ${error.responseJSON.msg || error.statusText}`);
                            } else if (error.responseText) {
                                console.error('Response Text:', error.responseText);
                                Swal.showValidationMessage(`Server Error: ${error.statusText}`);
                            } else {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            }
                        });
                    }
                }).then((result) => {
                    console.log('==================== APPROVE CALLBACK ====================');
                    console.log('result.isConfirmed:', result.isConfirmed);
                    console.log('result.value:', result.value);
                    console.log('result.isDismissed:', result.isDismissed);

                    // Check if user cancelled
                    if (result.isDismissed) {
                        console.log('? DISMISSED - User cancelled');
                        return;
                    }

                    // Check if we have a value (from preConfirm Promise resolution)
                    if (result.value && result.value.status) {
                        console.log('? APPROVE SUCCESS - Will process and reload');

                        // Update CSRF Token
                        try {
                            if (result.value.csrf_token) {
                                $('#csrfToken').val(result.value.csrf_token);
                                $('input[name="' + csrfName + '"]').val(result.value.csrf_token);
                                console.log('? CSRF token updated');
                            }
                        } catch (e) {
                            console.error('Error updating CSRF:', e);
                        }

                        // Show notification
                        try {
                            console.log('Showing toast:', result.value.msg);
                            showBootstrapAlert('success', result.value.msg);
                            console.log('? Toast shown');
                        } catch (e) {
                            console.error('Error showing toast:', e);
                        }

                        // RELOAD
                        console.log('? Setting up reload in 1 second...');
                        setTimeout(function () {
                            console.log('?? RELOADING NOW!');
                            window.location.reload(true);
                        }, 1000);
                    } else {
                        console.warn('? NO VALUE or FAILED - result.value:', result.value);
                    }
                    console.log('==================== END APPROVE CALLBACK ====================');
                });
            });

            $('#tableLaporan').on('click', '.btn-reject', function () {
                var id = $(this).data('id');
                $('#rejectId').val(id);
                $('#modalReject').modal('show');
            });

            $('#tableLaporan').on('click', '.btn-reason', function () {
                var reason = $(this).data('reason');
                // Use alert for reason viewing, or a modal. Alert is a bit weird for "viewing info".
                // But user wanted "no sweetalert". So I'll just use a browser alert or the bootstrap alert function (top fixed).
                // Or maybe a modal? There is no "Reason Modal" prepared.
                // I'll use the Bootstrap Alert for simplicity but with a longer timeout or just standard alert() for info reading.
                // Standard alert() is fine for reading text.
                alert("Alasan Perbaikan:\n\n" + reason);
            });

            // Include select2
            $('.select2').select2({
                dropdownParent: $('#modalBeritaAcara')
            });
            $('.select2-multiple').select2({
                dropdownParent: $('#modalBeritaAcara'),
                theme: 'bootstrap-5',
                closeOnSelect: false
            });
            // Select2 for siswa lookup
            $('.select2-siswa').select2({
                dropdownParent: $('#modalBeritaAcara'),
                theme: 'bootstrap-5',
                closeOnSelect: false,
                allowClear: true
            });

            // Mapel Select Change to input name for Title
            $('#selectMapel').on('change', function () {
                var nama = $(this).find(':selected').data('nama');
                $('#inputMapelNama').val(nama);
            });

            // Ruang Select Change
            $('#selectRuang').on('change', function () {
                var nama = $(this).find(':selected').data('nama');
                $('#inputRuangNama').val(nama);
            });

            // Sesi Select Change
            $('#selectSesi').on('change', function () {
                var nama = $(this).find(':selected').data('nama');
                $('#inputSesiNama').val(nama);
            });

            // Photo Preview
            $('.foto-input').on('change', function () {
                var input = this;
                var previewId = $(this).data('preview');
                var previewDiv = $('#' + previewId);
                var previewImg = previewDiv.find('img');

                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.attr('src', e.target.result);
                        previewDiv.show();
                    };
                    reader.readAsDataURL(input.files[0]);
                } else {
                    previewDiv.hide();
                    previewImg.attr('src', '');
                }
            });

            // Submit Form Berita Acara
            $('#formBeritaAcara').submit(function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('#btnSimpanBerita').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                    },
                    success: function (res) {
                        if (res.status) {
                            showBootstrapAlert('success', res.msg);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showBootstrapAlert('danger', res.msg);
                            $('#btnSimpanBerita').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Berita Acara');
                        }
                    },
                    error: function () {
                        showBootstrapAlert('danger', 'Terjadi kesalahan server');
                        $('#btnSimpanBerita').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Berita Acara');
                    }
                });
            });

            // Submit Form Pakta Integritas
            $('#formPaktaIntegritas').submit(function (e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function () {
                        $('#formPaktaIntegritas button[type=submit]').attr('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Menyimpan...');
                    },
                    success: function (res) {
                        if (res.status) {
                            $('#modalPaktaIntegritas').modal('hide');
                            showBootstrapAlert('success', res.msg);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showBootstrapAlert('danger', res.msg);
                            $('#formPaktaIntegritas button[type=submit]').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Pakta Integritas');
                        }
                    },
                    error: function () {
                        showBootstrapAlert('danger', 'Terjadi kesalahan server');
                        $('#formPaktaIntegritas button[type=submit]').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Pakta Integritas');
                    }
                });
            });

            // Handle Upload Type Selection
            $('.btn-upload-type').on('click', function (e) {
                e.preventDefault();
                var jenis = $(this).data('jenis');
                if (jenis === 'Berita Acara') {
                    // Reset to create mode
                    $('#editBeritaAcaraId').val('');
                    $('#formBeritaAcara').attr('action', '<?= base_url("laporan/save_berita_acara") ?>');
                    $('#modalBeritaAcaraTitle').html('<i class="bi bi-file-text me-2"></i>Form Berita Acara');
                    $('#btnSimpanBerita .btn-text').text('Simpan Berita Acara');
                    $('#inputFoto1').attr('required', true);
                    
                    $('#formBeritaAcara')[0].reset();
                    $('#selectMapel').val('').trigger('change');
                    $('#selectRuang').val('');
                    $('#selectSesi').val('');
                    $('.select2-multiple').val('').trigger('change');
                    $('.select2-siswa').val('').trigger('change');
                    $('input[type=file]').val('');
                    // Reset photo previews
                    $('#previewFoto1, #previewFoto2, #previewFoto3').hide().find('img').attr('src', '');
                    $('#modalBeritaAcara').modal('show');
                    return;
                }

                if (jenis === 'Pakta Integritas') {
                    // Reset to create mode
                    $('#editPaktaIntegritasId').val('');
                    $('#formPaktaIntegritas').attr('action', '<?= base_url("laporan/save_pakta_integritas") ?>');
                    $('#modalPaktaIntegritasTitle').html('<i class="bi bi-shield-check me-2"></i>Form Pakta Integritas');
                    $('#btnSimpanPakta .btn-text').text('Simpan Pakta Integritas');
                    
                    $('#formPaktaIntegritas')[0].reset();
                    $('#modalPaktaIntegritas').modal('show');
                    return;
                }

                $('#inputJenis').val(jenis);
                $('#modalUploadTitle').html('<i class="bi bi-cloud-upload me-2"></i>Upload ' + jenis);

                // Reset Form
                $('#formUpload')[0].reset();
                $('#customFile').val(null);
                $('.custom-file-label').html('Pilih file (PDF)');

                // Auto-fill Title based on Type for convenience
                var autoTitle = '';
                if (jenis === 'Berita Acara') autoTitle = 'Berita Acara Pelaksanaan Ujian';
                else if (jenis === 'Pakta Integritas') autoTitle = 'Pakta Integritas Pengawas';
                else if (jenis === 'Daftar Hadir Peserta') autoTitle = 'Daftar Hadir Peserta Ujian';
                else if (jenis === 'Laporan Pelaksanaan') autoTitle = 'Laporan Pelaksanaan Ujian';

                $('input[name="judul"]').val(autoTitle);

                // Show Modal
                $('#modalUpload').modal('show');
            });

            // =====================================================
            // Handle Edit Button Click
            // =====================================================
            $('#tableLaporan').on('click', '.btn-edit', function () {
                var id = $(this).data('id');
                var jenis = $(this).data('jenis');
                
                // Show loading
                Swal.fire({
                    title: 'Memuat data...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Fetch data from server
                $.ajax({
                    url: '<?= base_url("laporan/get_laporan/") ?>' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (res) {
                        Swal.close();
                        
                        if (!res.status) {
                            showBootstrapAlert('danger', res.msg || 'Gagal memuat data');
                            return;
                        }

                        var data = res.data;
                        var content = res.content;

                        if (jenis === 'Berita Acara') {
                            // Set to edit mode
                            $('#editBeritaAcaraId').val(id);
                            $('#formBeritaAcara').attr('action', '<?= base_url("laporan/update_berita_acara") ?>');
                            $('#modalBeritaAcaraTitle').html('<i class="bi bi-pencil me-2"></i>Edit Berita Acara');
                            $('#btnSimpanBerita .btn-text').text('Perbarui Berita Acara');
                            $('#inputFoto1').attr('required', false); // Photo not required for edit
                            
                            // Fill form with data
                            if (content) {
                                // Mapel
                                if (content.mapel) {
                                    $('#selectMapel').val(content.mapel).trigger('change');
                                    $('#inputMapelNama').val(content.mapel_nama || '');
                                }
                                
                                // Waktu
                                $('input[name="waktu_ujian"]').val(content.waktu_ujian || '');
                                $('input[name="jam_mulai"]').val(content.jam_mulai || '');
                                $('input[name="jam_selesai"]').val(content.jam_selesai || '');
                                
                                // Ruang
                                if (content.ruang) {
                                    $('#selectRuang').val(content.ruang);
                                    $('#inputRuangNama').val(content.ruang_nama || '');
                                }
                                
                                // Sesi
                                if (content.sesi) {
                                    $('#selectSesi').val(content.sesi);
                                    $('#inputSesiNama').val(content.sesi_nama || '');
                                }
                                
                                // Jumlah Peserta
                                $('input[name="jml_seharusnya"]').val(content.jml_seharusnya || '');
                                $('input[name="jml_hadir"]').val(content.jml_hadir || '');
                                $('input[name="jml_tidak_hadir"]').val(content.jml_tidak_hadir || '');
                                
                                // Siswa tidak hadir
                                if (content.siswa_tidak_hadir && content.siswa_tidak_hadir.length > 0) {
                                    var siswaIds = content.siswa_tidak_hadir.map(function(s) { return s.id; });
                                    $('#selectSiswaTidakHadir').val(siswaIds).trigger('change');
                                } else {
                                    $('#selectSiswaTidakHadir').val([]).trigger('change');
                                }
                                
                                // Text fields
                                $('textarea[name="uraian"]').val(content.uraian || '');
                                $('textarea[name="kendala"]').val(content.kendala || '');
                                $('textarea[name="solusi"]').val(content.solusi || '');
                            }
                            
                            // Show existing photos preview
                            if (data.file) {
                                var url1 = '<?= base_url("uploads/laporan/") ?>' + data.file;
                                $('#previewFoto1').show().find('img').attr('src', url1);
                            } else {
                                $('#previewFoto1').hide();
                            }
                            if (data.file_2) {
                                var url2 = '<?= base_url("uploads/laporan/") ?>' + data.file_2;
                                $('#previewFoto2').show().find('img').attr('src', url2);
                            } else {
                                $('#previewFoto2').hide();
                            }
                            if (data.file_3) {
                                var url3 = '<?= base_url("uploads/laporan/") ?>' + data.file_3;
                                $('#previewFoto3').show().find('img').attr('src', url3);
                            } else {
                                $('#previewFoto3').hide();
                            }
                            
                            $('#modalBeritaAcara').modal('show');
                            
                        } else if (jenis === 'Pakta Integritas') {
                            // Set to edit mode
                            $('#editPaktaIntegritasId').val(id);
                            $('#formPaktaIntegritas').attr('action', '<?= base_url("laporan/update_pakta_integritas") ?>');
                            $('#modalPaktaIntegritasTitle').html('<i class="bi bi-pencil me-2"></i>Edit Pakta Integritas');
                            $('#btnSimpanPakta .btn-text').text('Perbarui Pakta Integritas');
                            
                            // Fill form with data
                            if (content) {
                                $('input[name="nama"]').val(content.nama || '');
                                $('input[name="nip"]').val(content.nip || '');
                                $('input[name="jabatan"]').val(content.jabatan || '');
                                $('input[name="instansi"]').val(content.instansi || '');
                                $('input[name="no_hp"]').val(content.no_hp || '');
                                $('input[name="email"]').val(content.email || '');
                                $('input[name="provinsi"]').val(content.provinsi || '');
                                $('input[name="kota"]').val(content.kota || '');
                                $('input[name="tempat"]').val(content.tempat || '');
                                $('input[name="tanggal_pelaksanaan"]').val(content.tanggal_pelaksanaan || '');
                            }
                            
                            $('#modalPaktaIntegritas').modal('show');
                        }
                    },
                    error: function () {
                        Swal.close();
                        showBootstrapAlert('danger', 'Terjadi kesalahan server');
                    }
                });
            });
        });
    </script>