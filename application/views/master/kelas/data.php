<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <h6><?= $subjudul ?></h6>
                    </div>
                    <div class="card-tools">
                        <a type="button" href="<?= base_url('datakelas') ?>" class="btn btn-warning"
                           data-toggle="tooltip" title="Reload"><i class="fa fa-sync ml-1 mr-1"></i> Reload
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php $totalKelas = count($kelas); ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Tahun Pelajaran <?= $tp_active->tahun ?></h5>
                                <div class="text-muted">Semester <?= $smt_active->smt ?></div>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <span class="badge badge-primary px-3 py-2 text-sm">Total Kelas: <?= $totalKelas ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="mr-2 mb-2" data-toggle="tooltip" title="Tambah Kelas">
                                    <a href="<?= base_url('datakelas/add') ?>" type="button"
                                       class="btn btn-success">
                                        <i class="fas fa-plus mr-1"></i> Kelas
                                    </a>
                                </span>
                                <button type="button" data-toggle="modal" data-target="#bulkModal"
                                        class="btn btn-info mr-2 mb-2"><i class="fa fa-plus mr-1"></i> Sekaligus
                                </button>

                                <?php if ($smt_active->id_smt == '2') : ?>
                                    <span class="mr-2 mb-2" data-toggle="tooltip" title="Manajemen Kelas">
                                        <a href="<?= base_url('datakelas/manage') ?>" type="button"
                                           class="btn btn-primary">
                                            <i class="fas fa-tools mr-1"></i> Atur Kelas Semester
                                        </a>
                                    </span>
                                <?php endif; ?>
                                <?php if ($smt_active->id_smt == '1') : ?>
                                    <span class="mr-2 mb-2" data-toggle="tooltip" title="Kenaikan Kelas">
                                        <a href="<?= base_url('datakelas/kenaikan') ?>" type="button"
                                           class="btn btn-primary">
                                            <i class="fas fa-tools mr-1"></i> Kenaikan Kelas
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengelolaan Kelas</h5>
                        <ul class="pl-3 mb-0">
                            <li>
                                <span class="badge badge-success"><i class="fas fa-plus mr-1"></i> Kelas</span>
                                dan <span class="badge badge-info"><i class="fa fa-plus"></i> Sekaligus</span> digunakan untuk menambah kelas baru jika kelas tidak ada pada tahun atau semester sebelumnya
                            </li>
                            <li>
                                Jika kelas sudah ada di Tahun atau semester sebelumnya, maka anda bisa menyalin dari kelas tersebut dengan klik tombol
                                <?php if ($smt_active->id_smt == '2'): ?>
                                    <span class="badge badge-primary"><i class="fas fa-tools mr-1"></i> Atur Kelas Semester</span>
                                <?php else: ?>
                                    <span class="badge badge-primary"><i class="fas fa-tools mr-1"></i> Kenaikan Kelas</span>
                                <?php endif; ?>
                            </li>
                            <?php if ($smt_active->id_smt == '2'): ?>
                                <li>
                                    <span class="badge badge-primary"><i class="fas fa-tools mr-1"></i> Atur Kelas Semester</span>
                                    digunakan jika ingin menyalin semua data kelas dari SMT I ke SMT II
                                </li>
                            <?php else: ?>
                                <li>
                                    <span class="badge badge-primary"><i class="fas fa-tools mr-1"></i> Kenaikan Kelas</span>
                                    digunakan untuk mengatur kenaikan kelas, siswa akan otomatis dipindahkan ke
                                    tahun pelajaran berikutnya sesuai kelasnya
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <?php
                    //var_dump($kelas);
                    if ($totalKelas === 0) : ?>
                        <div class="col-12">
                            <div class="alert alert-default-warning shadow align-content-center" role="alert">
                                Belum ada data kelas untuk Tahun Pelajaran <b><?= $tp_active->tahun ?></b> Semester:
                                <b><?= $smt_active->smt ?></b>
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table id="table-kelas" class="w-100 table table-sm table-striped table-bordered table-hover">
                            <thead>
                            <tr class="alert alert-info">
                                <th height="40" class="text-center align-middle p-0">No.</th>
                                <th class="align-middle text-center">Nama Kelas</th>
                                <?php if ($setting->jenjang == '3') : ?>
                                    <th class="align-middle text-center">Jurusan</th>
                                <?php endif; ?>
                                <th class="align-middle text-center">Jumlah<br/>Siswa</th>
                                <th class="align-middle text-center">Wali Kelas</th>
                                <th class="align-middle text-center">Import<br/>Siswa</th>
                                <th class="text-center align-middle p-0" style="width: 180px"><span>Aksi</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $no = 1;
                            foreach ($kelas as $kls) : ?>
                                <tr>
                                    <td class="align-middle text-center"><?= $no ?></td>
                                    <td class="align-middle text-center"><?= $kls->nama_kelas ?></td>
                                    <?php if ($setting->jenjang == '3') : ?>
                                        <td class="align-middle text-center"><?= $kls->nama_jurusan ?></td>
                                    <?php endif; ?>
                                    <td class="align-middle text-center"><?= $kls->jml_siswa ?></td>
                                    <td class="align-middle text-center"><?= $kls->nama_guru ?></td>
                                    <td class="align-middle text-center">
                                    <span data-toggle="tooltip" title="Lihat Detail Kelas">
                                            <!-- ikasmedia -->
										<a type="button" href="<?= base_url('datasiswa/add/' . $kls->id_kelas) ?>"
                                           class="btn btn-primary btn-xs mr-1">
											<i class="fa fa-upload"></i>
										</a>
									</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group btn-group-xs">
                                <span data-toggle="tooltip" title="Lihat Detail Kelas">
                                            <!-- ikasmedia -->
										<a type="button" href="<?= base_url('datakelas/detail/' . $kls->id_kelas) ?>"
                                           class="btn btn-info btn-xs mr-1">
											<i class="fa fa-search"></i>
										</a>
									</span>
                                            <span data-toggle="tooltip" title="Edit Kelas">
                                            <!-- ikasmedia -->
										<a type="button" href="<?= base_url('datakelas/edit/' . $kls->id_kelas) ?>"
                                           class="btn btn-warning btn-xs mr-1">
											<i class="fa fa-pencil-alt text-white"></i>
										</a>
									</span>
                                            <!-- ikasmedia -->
                                            <button data-id="<?= $kls->id_kelas ?>" type="button"
                                                    class="btn-xs btn btn-danger hapuskelas" data-toggle="tooltip"
                                                    title="Hapus Data Kelas">
                                                <i class="far fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $no++; endforeach;
                            endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="bulkModal" tabindex="-1" role="dialog" aria-labelledby="bulkModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkModalLabel">Tambah Kelas Sekaligus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open('', array('id' => 'bulk-create')) ?>
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-4 col-form-label">Format Tingkat*</label>
                    <div class="col-8">
                        <select id="formatAngka" name="formatAngka" class="form-control" required>
                            <option value="" selected>Pilih Format</option>
                            <option value="romawi">Angka Romawi</option>
                            <option value="biasa">Angka Biasa</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-4 col-form-label">Tingkat*</label>
                    <div class="col-8">
                        <select id="level" name="level" class="form-control" required="">
                            <option value="0" selected disabled>Pilih Level Kelas</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-4 col-form-label">Jumlah*</label>
                    <div class="col-8">
                        <input type="number" id="jumlah" name="jumlah" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-4 col-form-label">Format Kelas*</label>
                    <div class="col-8">
                        <select id="char" name="char" class="form-control" required="">
                            <option value="0" selected disabled>Pilih jenis</option>
                            <option value="huruf">Huruf</option>
                            <option value="angka">Angka</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-4 col-form-label">Mulai dari*</label>
                    <div class="col-8">
                        <select id="awal" name="awal" class="form-control" required="">
                            <option value="0" selected disabled>Kelas awal</option>
                        </select>
                    </div>
                </div>

                <div class="alert alert-default-warning border border-secondary p-2">
                    <div class="text-bold">Preview Hasil:</div>
                    <div id="hasil-kelas" class="mt-1">
                        <span class="font-italic">Isi semua field untuk melihat hasil</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    const jenjang = '<?= $setting->jenjang ?>';
    let lvl, jml, char, awal, formatAngka;
    let tempArrKelas = [];
    $(document).ready(function () {
        ajaxcsrf();

        $('#table-kelas').dataTable({
            paging: false,
            order: [],
            "aoColumnDefs": [
                { "bSortable": false, "aTargets": [0,5]},
            ],
        })

        $('.hapuskelas').click(function (e) {
            var kelas = $(this).data('id');
            swal.fire({
                title: "Hapus Data Kelas?",
                text: "Kelas beserta jumlah siswa akan dihapus",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'datakelas/hapus/'+kelas,
                        method:"GET",
                        success: function (respon) {
                            console.log(respon);
                            if (respon.kelas) {
                                window.location.href = base_url +'datakelas';
                            } else {
                                swal.fire({
                                    title: "Gagal",
                                    text: "Tidak bisa menghapus kelas",
                                    icon: "error"
                                });
                            }
                        },
                        error: function (xhr, error, status) {
                            console.log(xhr.responseText);
                            swal.fire({
                                title: "Gagal",
                                text: "Tidak bisa menghapus kelas",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });

        const opsiFormat = $('#formatAngka');
        const opsiLevel = $('#level');
        opsiFormat.change(function () {
            formatAngka = $(this).val();
            opsiLevel.empty();
            opsiLevel.prepend('<option value="" selected disabled>Pilih Level Kelas</option>');
            let arrLvl = [];
            if (jenjang === '1') {
                arrLvl = ['1', '2', '3', '4', '5', '6'];
            } else if (jenjang === '2') {
                arrLvl = ['7', '8', '9'];
            } else if (jenjang === '3') {
                arrLvl = ['10', '11', '12'];
            }
            const formatValue = (val) => {
                return formatAngka === 'romawi' ? toRoman(val) : val;
            };
            for (const level of arrLvl) {
                opsiLevel.append('<option value="' + level + '">' + formatValue(level) + '</option>');
            }
        });

        opsiLevel.change(function () {
            lvl = $(this).val();
        });
        const opsiJumlah = $('#jumlah');
        opsiJumlah.change(function () {
            jml = $(this).val();
        });
        const opsiChar = $('#char');
        const opsiAwal = $('#awal');
        opsiChar.change(function () {
            char = $(this).val();
            opsiAwal.empty();
            opsiAwal.append('<option disabled selected>Kelas awal</option>');
            if (char === 'angka') {
                for (let i = 1; i <= 20; i++) {
                    opsiAwal.append('<option value="' + i + '">' + i + '</option>');
                }
            } else if (char === 'huruf') {
                const alpha = Array.from(Array(26)).map((e, i) => i + 65);
                const alphabet = alpha.map((x) => String.fromCharCode(x));
                for (const string of alphabet) {
                    opsiAwal.append('<option value="' + string + '">' + string + '</option>');
                }
            }
        });
        opsiAwal.change(function () {
            awal = $(this).val();
        });

        $('#level, #jumlah, #char, #awal, #formatAngka').change(function () {
            if ($('#level').val() && $('#jumlah').val() && $('#char').val() && $('#awal').val() && $('#formatAngka').val()) {
                generateNamaKelas();
            } else {
                $('#hasil-kelas').html('<span class="font-italic">Isi semua field untuk melihat hasil</span>');
            }
        });

        $('#bulk-create').submit(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $('#bulkModal').modal('hide').data('bs.modal', null);
            let form = new FormData($(this)[0])
            form.append('kelas', JSON.stringify(tempArrKelas));
            $.ajax({
                url: base_url + 'datakelas/bulk_create',
                method: "POST",
                processData: false,
                contentType: false,
                data: form,
                success: function (respon) {
                    console.log('bulk', respon);
                    if (respon.success) {
                        swal.fire({
                            title: "Berhasil",
                            text: respon.insert + " Kelas berhasil ditambahkan",
                            icon: "success"
                        }).then(result => {
                            if (result.value) {
                                window.location.href = base_url +'datakelas';
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Gagal",
                            text: respon.insert + " Kelas gagal disimpan",
                            icon: "error"
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr);
                    Swal.fire({
                        title: "ERROR!",
                        html: xhr.responseText,
                        icon: "error"
                    });
                }
            });

            tempArrKelas = [];
        });

        $('#bulkModal').on('hidden.bs.modal', function () {
            // Reset semua input
            $('#bulkModal form')[0].reset();
            $('#hasil-kelas').html('<span class="font-italic">Isi semua field untuk melihat hasil</span>');

            // Reset variabel JS
            lvl = null;
            jml = null;
            char = null;
            awal = null;
            formatAngka = null;
            tempArrKelas = [];
        });
    });

    function generateNamaKelas() {
        tempArrKelas = [];
        const jumlah = parseInt(jml);
        const hasil = [];

        // Format angka biasa atau romawi
        const formatValue = (val) => {
            return formatAngka === 'romawi' ? toRoman(val) : val;
        };

        if (char === 'angka') {
            let start = parseInt(awal);
            for (let i = 0; i < jumlah; i++) {
                let kelas = {};
                kelas['level_id'] = lvl;
                kelas['nama_kelas'] = `${formatValue(lvl)} ${start + i}`;
                kelas['kode_kelas'] = `${formatValue(lvl)}${start + i}`;
                kelas['id_tp'] = '<?= $tp_active->id_tp ?>';
                kelas['id_smt'] = '<?= $smt_active->id_smt ?>';
                kelas['jumlah_siswa'] = '[]';
                tempArrKelas.push(kelas);
                hasil.push(`${formatValue(lvl)} ${start + i}`);
            }
        } else if (char === 'huruf') {
            const alpha = Array.from(Array(26)).map((_, i) => String.fromCharCode(65 + i));
            let startIdx = alpha.indexOf(awal.toUpperCase());
            for (let i = 0; i < jumlah; i++) {
                let kelas = {};
                kelas['level_id'] = lvl;
                kelas['nama_kelas'] = `${formatValue(lvl)} ${alpha[startIdx + i]}`;
                kelas['kode_kelas'] = `${formatValue(lvl)}${alpha[startIdx + i]}`;
                kelas['id_tp'] = '<?= $tp_active->id_tp ?>';
                kelas['id_smt'] = '<?= $smt_active->id_smt ?>';
                kelas['jumlah_siswa'] = '[]';
                tempArrKelas.push(kelas);
                hasil.push(`${formatValue(lvl)} ${alpha[startIdx + i]}`);
            }
        }
        $('#hasil-kelas').html(hasil.map((n) => `<span class="badge badge-info text-sm">${n}</span>`).join(' '));
    }

    function toRoman(num) {
        const roman = [
            ['M',1000],['CM',900],['D',500],['CD',400],
            ['C',100],['XC',90],['L',50],['XL',40],
            ['X',10],['IX',9],['V',5],['IV',4],['I',1]
        ];
        let result = '';
        for (const [r, v] of roman) {
            while (num >= v) {
                result += r;
                num -= v;
            }
        }
        return result;
    }
</script>
