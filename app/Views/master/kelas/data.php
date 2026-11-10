<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="card card-default my-shadow mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-door-closed me-2"></i>
                    <?= esc($judul ?? 'Data Kelas') ?>
                </h3>
                <div class="card-tools">
                    <button type="button" onclick="location.reload()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i>Reload
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary ms-1 btn-sync-dapodik" data-entitas="rombel">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i>Sync Dapodik
                    </button>
                    <a href="<?= site_url('datakelas/add') ?>" class="btn btn-sm btn-primary ms-1">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Kelas
                    </a>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#bulkModal"
                        class="btn btn-sm btn-success ms-1">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Sekaligus
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php $totalKelas = count($kelas); ?>
                <div class="mb-3">
                    <span class="badge bg-secondary px-3 py-2">Tahun Pelajaran: <?= $tp_active->tahun ?> | Semester:
                        <?= $smt_active->smt ?> | Total Kelas: <?= $totalKelas ?></span>
                </div>

                <?php if ($totalKelas === 0): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>Belum ada data kelas untuk Tahun Pelajaran
                        <b><?= $tp_active->tahun ?></b> Semester <b><?= $smt_active->smt ?></b>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="table-kelas" class="w-100 table table-striped table-bordered table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="40" class="align-middle text-center p-0">No.</th>
                                    <th>Nama Kelas</th>
                                    <?php if ($setting->jenjang == '3'): ?>
                                        <th>Jurusan</th>
                                    <?php endif; ?>
                                    <th width="100" class="align-middle text-center">Jumlah Siswa</th>
                                    <th>Wali Kelas</th>
                                    <th width="100" class="align-middle text-center">Import</th>
                                    <th width="150" class="align-middle text-center p-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($kelas as $kls): ?>
                                    <tr>
                                        <td class="align-middle text-center"><?= $no++ ?></td>
                                        <td class="align-middle fw-bold">
                                            <?= $kls->nama_kelas ?>
                                            <?php if (!empty($kls->dapo_id) || !empty($kls->rombel_id)): ?>
                                                <span class="badge bg-success ms-2" title="ID Dapodik: <?= $kls->dapo_id ?? $kls->rombel_id ?>"><i class="bi bi-cloud-check-fill me-1"></i>Dapodik</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border ms-2" style="font-weight: normal;"><i class="bi bi-cloud-slash me-1"></i>Lokal</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($setting->jenjang == '3'): ?>
                                            <td class="align-middle"><?= $kls->nama_jurusan ?></td>
                                        <?php endif; ?>
                                        <td class="align-middle text-center"><?= $kls->jml_siswa ?></td>
                                        <td class="align-middle"><?= $kls->nama_guru ?? '-' ?></td>
                                        <td class="align-middle text-center">
                                             <a href="<?= site_url('datasiswa/add/' . $kls->id_kelas) ?>"
                                                 class="btn btn-action btn-action-warning" title="Import Siswa">
                                                 <i class="bi bi-upload"></i>
                                             </a>
                                         </td>
                                         <td class="align-middle text-center">
                                             <div class="btn-action-group justify-content-center">
                                                 <a href="<?= site_url('datakelas/detail/' . $kls->id_kelas) ?>"
                                                     class="btn btn-action btn-action-magic" title="Detail Kelas">
                                                     <i class="bi bi-eye-fill"></i>
                                                 </a>
                                                 <a href="<?= site_url('datakelas/edit/' . $kls->id_kelas) ?>"
                                                     class="btn btn-action btn-action-edit" title="Edit Kelas">
                                                     <i class="bi bi-pencil-fill"></i>
                                                 </a>
                                                 <button data-id="<?= $kls->id_kelas ?>" type="button"
                                                     class="btn btn-action btn-action-delete hapuskelas" title="Hapus Kelas">
                                                     <i class="bi bi-trash-fill"></i>
                                                 </button>
                                             </div>
                                         </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bulk Create -->
<div class="modal fade" id="bulkModal" tabindex="-1" aria-labelledby="bulkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkModalLabel">Tambah Kelas Sekaligus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulk-create">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-4 col-form-label">Format Tingkat*</label>
                        <div class="col-8">
                            <select id="formatAngka" name="formatAngka" class="form-select" required>
                                <option value="" selected disabled>Pilih Format</option>
                                <option value="romawi">Angka Romawi</option>
                                <option value="biasa">Angka Biasa</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-4 col-form-label">Tingkat*</label>
                        <div class="col-8">
                            <select id="level" name="level" class="form-select" required>
                                <option value="" selected disabled>Pilih Level Kelas</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-4 col-form-label">Jumlah*</label>
                        <div class="col-8">
                            <input type="number" id="jumlah" name="jumlah" class="form-control" required min="1">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-4 col-form-label">Format Kelas*</label>
                        <div class="col-8">
                            <select id="char" name="char" class="form-select" required>
                                <option value="" selected disabled>Pilih jenis</option>
                                <option value="huruf">Huruf</option>
                                <option value="angka">Angka</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-4 col-form-label">Mulai dari*</label>
                        <div class="col-8">
                            <select id="awal" name="awal" class="form-select" required>
                                <option value="" selected disabled>Kelas awal</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-light border p-2">
                        <div class="fw-bold">Preview Hasil:</div>
                        <div id="hasil-kelas" class="mt-1">
                            <span class="fst-italic text-muted">Isi semua field untuk melihat hasil</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const jenjang = '<?= $setting->jenjang ?>';
    // base_url is already defined in main layout
    let lvl, jml, char, awal, formatAngka;
    let tempArrKelas = [];

    $(document).ready(function () {
        $('#table-kelas').DataTable({
            paging: false,
            order: [],
            columnDefs: [
                { orderable: false, targets: [0, 5, 6] },
            ],
        });

        $('.hapuskelas').click(function (e) {
            var kelasId = $(this).data('id');
            Swal.fire({
                title: "Hapus Data Kelas?",
                text: "Kelas beserta penempatan siswa akan dihapus dari semester ini.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Hapus"
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: base_url + 'datakelas/hapus/' + kelasId,
                        method: "GET",
                        success: function (respon) {
                            if (respon.kelas) {
                                Swal.fire("Berhasil", "Data kelas berhasil dihapus", "success").then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire("Gagal", respon.message || "Tidak bisa menghapus kelas", "error");
                            }
                        },
                        error: function (xhr) {
                            Swal.fire("Error", "Internal Server Error", "error");
                        }
                    });
                }
            });
        });

        $('#formatAngka').change(function () {
            formatAngka = $(this).val();
            const opsiLevel = $('#level');
            opsiLevel.empty();
            opsiLevel.append('<option value="" selected disabled>Pilih Level Kelas</option>');

            let arrLvl = [];
            if (jenjang === '1') arrLvl = ['1', '2', '3', '4', '5', '6'];
            else if (jenjang === '2') arrLvl = ['7', '8', '9'];
            else if (jenjang === '3') arrLvl = ['10', '11', '12'];

            const formatValue = (val) => formatAngka === 'romawi' ? toRoman(val) : val;

            arrLvl.forEach(level => {
                opsiLevel.append(`<option value="${level}">${formatValue(level)}</option>`);
            });
        });

        $('#char').change(function () {
            char = $(this).val();
            const opsiAwal = $('#awal');
            opsiAwal.empty();
            opsiAwal.append('<option disabled selected value="">Kelas awal</option>');

            if (char === 'angka') {
                for (let i = 1; i <= 20; i++) {
                    opsiAwal.append(`<option value="${i}">${i}</option>`);
                }
            } else if (char === 'huruf') {
                const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
                alphabet.forEach(string => {
                    opsiAwal.append(`<option value="${string}">${string}</option>`);
                });
            }
        });

        $('#level, #jumlah, #char, #awal, #formatAngka').change(function () {
            lvl = $('#level').val();
            jml = $('#jumlah').val();
            char = $('#char').val();
            awal = $('#awal').val();
            formatAngka = $('#formatAngka').val();

            if (lvl && jml && char && awal && formatAngka) {
                generateNamaKelas();
            } else {
                $('#hasil-kelas').html('<span class="fst-italic text-muted">Isi semua field untuk melihat hasil</span>');
            }
        });

        $('#bulk-create').submit(function (e) {
            e.preventDefault();
            $('#bulkModal').modal('hide');

            let formData = new FormData(this);
            formData.append('kelas', JSON.stringify(tempArrKelas));

            $.ajax({
                url: base_url + 'datakelas/bulk_create',
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (respon) {
                    if (respon.success) {
                        Swal.fire("Berhasil", respon.insert + " Kelas berhasil ditambahkan", "success").then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire("Gagal", "Gagal menyimpan data", "error");
                    }
                },
                error: function (xhr) {
                    Swal.fire("Error", xhr.responseText, "error");
                }
            });
        });
    });

    function generateNamaKelas() {
        tempArrKelas = [];
        const jumlah = parseInt(jml);
        const hasil = [];
        const formatValue = (val) => formatAngka === 'romawi' ? toRoman(val) : val;

        if (char === 'angka') {
            let start = parseInt(awal);
            for (let i = 0; i < jumlah; i++) {
                let name = `${formatValue(lvl)} ${start + i}`;
                tempArrKelas.push({
                    level_id: lvl,
                    nama_kelas: name,
                    kode_kelas: `${formatValue(lvl)}${start + i}`,
                    id_tp: '<?= $tp_active->id_tp ?>',
                    id_smt: '<?= $smt_active->id_smt ?>',
                    jumlah_siswa: '[]'
                });
                hasil.push(name);
            }
        } else if (char === 'huruf') {
            const alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
            let startIdx = alpha.indexOf(awal.toUpperCase());
            for (let i = 0; i < jumlah; i++) {
                let charVal = alpha[startIdx + i];
                let name = `${formatValue(lvl)} ${charVal}`;
                tempArrKelas.push({
                    level_id: lvl,
                    nama_kelas: name,
                    kode_kelas: `${formatValue(lvl)}${charVal}`,
                    id_tp: '<?= $tp_active->id_tp ?>',
                    id_smt: '<?= $smt_active->id_smt ?>',
                    jumlah_siswa: '[]'
                });
                hasil.push(name);
            }
        }
        $('#hasil-kelas').html(hasil.map(n => `<span class="badge bg-info text-sm me-1">${n}</span>`).join(' '));
    }

    function toRoman(num) {
        const roman = [
            ['M', 1000], ['CM', 900], ['D', 500], ['CD', 400],
            ['C', 100], ['XC', 90], ['L', 50], ['XL', 40],
            ['X', 10], ['IX', 9], ['V', 5], ['IV', 4], ['I', 1]
        ];
        let result = '';
        let n = parseInt(num);
        for (const [r, v] of roman) {
            while (n >= v) {
                result += r;
                n -= v;
            }
        }
        return result;
    }
</script>
<?= $this->endSection() ?>