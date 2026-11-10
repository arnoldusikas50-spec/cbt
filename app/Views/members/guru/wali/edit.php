<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$fotoSiswa = (!empty($siswa->foto) && file_exists(FCPATH . $siswa->foto)) 
    ? base_url($siswa->foto) 
    : (($siswa->jenis_kelamin == 'P') ? base_url('assets/img/siswa-p.png') : base_url('assets/img/siswa-l.png'));
?>

    <!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= esc($judul ?? 'Halaman') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
</div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
            <div class="col-md-4">
                <div class="card card-default my-shadow border-0 overflow-hidden mb-4">
                    <div class="card-header bg-gradient-info text-white py-3">
                        <h3 class="card-title fw-bold"><i class="bi bi-person-badge me-2"></i>Profil Siswa</h3>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="position-relative d-inline-block mb-4">
                            <img src="<?= $fotoSiswa ?>" class="rounded-circle shadow border border-4 border-white" width="150" height="150" style="object-fit: cover;" alt="Foto Siswa">
                            <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle p-2 shadow" data-bs-toggle="modal" data-bs-target="#editFotoModal" title="Ganti Foto">
                                <i class="bi bi-camera-fill"></i>
                            </button>
                        </div>
                        <h4 class="fw-bold mb-1"><?= $siswa->nama ?></h4>
                        <p class="text-muted small mb-4">NIS: <?= $siswa->nis ?> | NISN: <?= $siswa->nisn ?></p>
                        
                        <div class="d-grid gap-2 px-4 mt-4">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" onclick="deleteImage(true)">
                                <i class="bi bi-trash me-1"></i> Hapus Foto
                            </button>
                            <button type="button" class="btn btn-warning btn-sm rounded-pill text-white shadow-sm mt-2" data-bs-toggle="modal" data-bs-target="#editLoginModal">
                                <i class="bi bi-shield-lock me-1"></i> Ganti Username / Password
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="d-block d-md-none mb-4">
                    <a href="<?= base_url('walisiswa') ?>" class="btn btn-danger w-100 rounded-pill shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>

            <div class="col-md-8">
                <form id="form-siswa" action="<?= base_url('walisiswa/updatedata') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_siswa" value="<?= $siswa->id_siswa ?>">
                    
                    <div class="card card-default my-shadow border-0">
                        <div class="card-header p-2 bg-light border-bottom-0">
                            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#tab-siswa" type="button">Siswa</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#tab-bio" type="button">Detail</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button">Orang Tua</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#tab-wali" type="button">Wali</button>
                                </li>
                            </ul>
                            <div class="ms-auto pe-2 d-none d-sm-flex align-items-center">
                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm me-2">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                                <a href="<?= base_url('walisiswa') ?>" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-x-lg me-1"></i> Batal
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 pt-4">
                            <div class="tab-content" id="pills-tabContent">
                                <!-- Tab Siswa -->
                                <div class="tab-pane fade show active" id="tab-siswa" role="tabpanel">
                                    <h5 class="fw-bold mb-4 border-bottom pb-2">Informasi Akademik</h5>
                                    
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label fw-bold">Nama Lengkap</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control rounded-3" name="nama" value="<?= $siswa->nama ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label fw-bold">NIS</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control rounded-3" name="nis" value="<?= $siswa->nis ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label fw-bold">NISN</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control rounded-3" name="nisn" value="<?= $siswa->nisn ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label fw-bold">Jenis Kelamin</label>
                                        <div class="col-sm-8">
                                            <select class="form-select rounded-3" name="jenis_kelamin" required>
                                                <option value="L" <?= $siswa->jenis_kelamin == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                <option value="P" <?= $siswa->jenis_kelamin == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label fw-bold">Diterima di Kelas</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control rounded-3" name="kelas_awal" value="<?= $siswa->kelas_awal ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label fw-bold">Sekolah Asal</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control rounded-3" name="sekolah_asal" value="<?= $siswa->sekolah_asal ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label fw-bold">Tgl. Diterima</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control rounded-3 datepicker" name="tahun_masuk" value="<?= $siswa->tahun_masuk ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tab Bio -->
                                <div class="tab-pane fade" id="tab-bio" role="tabpanel">
                                    <h5 class="fw-bold mb-4 border-bottom pb-2">Biodata Pribadi</h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Tempat Lahir</label>
                                            <input type="text" class="form-control rounded-3" name="tempat_lahir" value="<?= $siswa->tempat_lahir ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Tanggal Lahir</label>
                                            <input type="text" class="form-control rounded-3 datepicker" name="tanggal_lahir" value="<?= $siswa->tanggal_lahir ?>">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Agama</label>
                                            <select class="form-select rounded-3" name="agama">
                                                <option value="">Pilih Agama</option>
                                                <?php foreach (['Islam', 'Katolik', 'Protestan', 'Hindu', 'Budha', 'Konghucu'] as $agama): ?>
                                                    <option value="<?= $agama ?>" <?= $siswa->agama == $agama ? 'selected' : '' ?>><?= $agama ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Status Keluarga</label>
                                            <select class="form-select rounded-3" name="status_keluarga">
                                                <option value="1" <?= $siswa->status_keluarga == '1' ? 'selected' : '' ?>>Anak Kandung</option>
                                                <option value="2" <?= $siswa->status_keluarga == '2' ? 'selected' : '' ?>>Anak Tiri</option>
                                                <option value="3" <?= $siswa->status_keluarga == '3' ? 'selected' : '' ?>>Anak Angkat</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Anak Ke</label>
                                            <input type="number" class="form-control rounded-3" name="anak_ke" value="<?= $siswa->anak_ke ?>">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold small">No. HP / WhatsApp</label>
                                            <input type="text" class="form-control rounded-3" name="hp" value="<?= $siswa->hp ?>">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label fw-bold small">Alamat Lengkap</label>
                                            <textarea class="form-control rounded-3" name="alamat" rows="2"><?= $siswa->alamat ?></textarea>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">RT</label>
                                            <input type="text" class="form-control rounded-3 text-center" name="rt" value="<?= $siswa->rt ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">RW</label>
                                            <input type="text" class="form-control rounded-3 text-center" name="rw" value="<?= $siswa->rw ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Kelurahan</label>
                                            <input type="text" class="form-control rounded-3" name="kelurahan" value="<?= $siswa->kelurahan ?>">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Kecamatan</label>
                                            <input type="text" class="form-control rounded-3" name="kecamatan" value="<?= $siswa->kecamatan ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Kabupaten</label>
                                            <input type="text" class="form-control rounded-3" name="kabupaten" value="<?= $siswa->kabupaten ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Provinsi</label>
                                            <input type="text" class="form-control rounded-3" name="provinsi" value="<?= $siswa->provinsi ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Kode Pos</label>
                                            <input type="text" class="form-control rounded-3" name="kode_pos" value="<?= $siswa->kode_pos ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tab Ortu -->
                                <div class="tab-pane fade" id="tab-ortu" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-md-6 border-end">
                                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-fill me-1"></i>Data Ayah</h6>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Nama Ayah</label>
                                                <input type="text" class="form-control rounded-3" name="nama_ayah" value="<?= $siswa->nama_ayah ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Tgl. Lahir</label>
                                                <input type="text" class="form-control rounded-3 datepicker" name="tgl_lahir_ayah" value="<?= $siswa->tgl_lahir_ayah ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Pendidikan</label>
                                                <input type="text" class="form-control rounded-3" name="pendidikan_ayah" value="<?= $siswa->pendidikan_ayah ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Pekerjaan</label>
                                                <input type="text" class="form-control rounded-3" name="pekerjaan_ayah" value="<?= $siswa->pekerjaan_ayah ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">No. HP</label>
                                                <input type="text" class="form-control rounded-3" name="nohp_ayah" value="<?= $siswa->nohp_ayah ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3 text-danger"><i class="bi bi-person-fill me-1"></i>Data Ibu</h6>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Nama Ibu</label>
                                                <input type="text" class="form-control rounded-3" name="nama_ibu" value="<?= $siswa->nama_ibu ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Tgl. Lahir</label>
                                                <input type="text" class="form-control rounded-3 datepicker" name="tgl_lahir_ibu" value="<?= $siswa->tgl_lahir_ibu ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Pendidikan</label>
                                                <input type="text" class="form-control rounded-3" name="pendidikan_ibu" value="<?= $siswa->pendidikan_ibu ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Pekerjaan</label>
                                                <input type="text" class="form-control rounded-3" name="pekerjaan_ibu" value="<?= $siswa->pekerjaan_ibu ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">No. HP</label>
                                                <input type="text" class="form-control rounded-3" name="nohp_ibu" value="<?= $siswa->nohp_ibu ?>">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Alamat Orang Tua</label>
                                            <textarea class="form-control rounded-3" name="alamat_ayah" rows="2" placeholder="Sama dengan alamat siswa jika dikosongkan"><?= $siswa->alamat_ayah ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tab Wali -->
                                <div class="tab-pane fade" id="tab-wali" role="tabpanel">
                                    <h5 class="fw-bold mb-4 border-bottom pb-2">Informasi Wali (Jika Ada)</h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Nama Wali</label>
                                            <input type="text" class="form-control rounded-3" name="nama_wali" value="<?= $siswa->nama_wali ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Tgl. Lahir</label>
                                            <input type="text" class="form-control rounded-3 datepicker" name="tgl_lahir_wali" value="<?= $siswa->tgl_lahir_wali ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Pendidikan</label>
                                            <input type="text" class="form-control rounded-3" name="pendidikan_wali" value="<?= $siswa->pendidikan_wali ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Pekerjaan</label>
                                            <input type="text" class="form-control rounded-3" name="pekerjaan_wali" value="<?= $siswa->pekerjaan_wali ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">No. HP</label>
                                            <input type="text" class="form-control rounded-3" name="nohp_wali" value="<?= $siswa->nohp_wali ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Alamat Wali</label>
                                            <textarea class="form-control rounded-3" name="alamat_wali" rows="2"><?= $siswa->alamat_wali ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-light p-3 d-flex d-sm-none justify-content-between">
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm flex-fill me-2">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="<?= base_url('walisiswa') ?>" class="btn btn-outline-danger rounded-pill px-3 shadow-sm">
                                Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Foto -->
<div class="modal fade" id="editFotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Ganti Foto Profil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <form id="form-upload-foto">
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <input type="file" id="input-foto" name="foto" class="dropify" data-max-file-size="2M" data-allowed-file-extensions="jpg jpeg png" data-default-file="<?= $fotoSiswa ?>" />
                    </div>
                    <p class="text-muted small">Max file size 2MB. Format: JPG, JPEG, PNG.</p>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-sm btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                <button type="button" id="btn-save-foto" class="btn btn-sm btn-primary px-4 rounded-pill shadow-sm">Simpan Foto</button>
            </div>
        </div>
    </div>
</div>

<script>
    var idSiswa = '<?= $siswa->id_siswa ?>';
    
    $(document).ready(function() {
        $('.datepicker').datetimepicker({
            timepicker: false,
            format: 'Y-m-d',
            scrollInput: false
        });

        var drEvent = $('.dropify').dropify({
            messages: {
                'default': 'Geser foto ke sini atau klik',
                'replace': 'Klik atau geser untuk mengganti',
                'remove': 'Hapus',
                'error': 'Ups, ada kesalahan.'
            }
        });

        $('#btn-save-foto').on('click', function() {
            var input = $('#input-foto')[0];
            if (input.files && input.files[0]) {
                var formData = new FormData($('#form-upload-foto')[0]);
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...');
                
                $.ajax({
                    url: base_url + 'walisiswa/uploadfile/' + idSiswa,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status) {
                            Swal.fire("Berhasil", "Foto profil telah diperbarui", "success").then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire("Gagal", "Unggah foto gagal", "error");
                        }
                    },
                    complete: function() {
                        $('#btn-save-foto').prop('disabled', false).html('Simpan Foto');
                    }
                });
            } else {
                Swal.fire("Peringatan", "Pilih file foto terlebih dahulu", "warning");
            }
        });

        $('#form-siswa').on('submit', function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            var oldText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status) {
                        Swal.fire("Berhasil", res.text, "success").then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Gagal", res.text, "error");
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).html(oldText);
                }
            });
        });
    });

    function deleteImage(refresh) {
        Swal.fire({
            title: "Hapus Foto?",
            text: "Foto akan dihapus dan kembali ke default.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + "walisiswa/deletefile/" + idSiswa,
                    type: "POST",
                    data: { src: '<?= $siswa->foto ?>' },
                    success: function(res) {
                        if (res.status) {
                            if (refresh) window.location.reload();
                        }
                    }
                });
            }
        });
    }
</script>

<?= $this->endSection() ?>
