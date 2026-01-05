<?php
$kelas_blm_ada = 0;

$kelass = $buat_kelas_baru[$level ?? '-'] ?? [];
$searchId = array_search('1', array_column($tp, 'active'));
$tpBefore = $tp[$searchId - 1] ?? null;
?>

<?= form_open('', array('id' => 'managekelas')) ?>
<div class="modal fade" id="addDialog" tabindex="-1" role="dialog" aria-labelledby="addDialogLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customDialogLabel">Buat Kelas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup">

                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class='col-12 table-responsive'>
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th class="text-center">TP <?= $tpBefore->tahun ?></th>
                                    <th class="text-center">TP <?= $tp_active->tahun ?></th>
                                    <th class="text-center align-middle">Ket.</th>
                                    <th class="text-center align-middle">
                                        <input class="check my-checkbox" type="checkbox" id="check-all">
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <?php
                                $no = 0;
                                foreach ($kelass as $key => $kls): ?>
                                    <tr>
                                        <td class="text-center align-middle">
                                            <?= $kelas_lama[$key] ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?= $kls ?>
                                            <?php if (!in_array($kls, $kelas_lama)): ?>
                                                <input type="hidden" name="kelas_dua[<?= $key ?>]" value="<?= $kls ?>" />
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (in_array($kls, $kelas_lama)): ?>
                                                <span class="text-success">Sdh ada</span>
                                            <?php else:
                                                $kelas_blm_ada++; ?>
                                                <span class="text-danger">Blm ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if (!in_array($kls, $kelas_lama)): ?>
                                                <input type="checkbox" name="kelas_satu[]" value="<?= $key ?>"
                                                    class="check check-kelas my-checkbox" />
                                            <?php else: ?>
                                                <span class="text-success"><i class="bi bi-check"></i></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $no++;
                                endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i><span
                        class="ms-1">Simpan</span></button>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>

<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <?= $subjudul ?>
                    </div>

                    <a href="<?= base_url('datakelas') ?>" type="button" class="btn btn-sm btn-danger float-end">
                        <i class="bi bi-arrow-left-square"></i><span
                            class="d-none d-sm-inline-block ms-1">Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    if ($tpBefore): ?>
                        <div class="alert alert-default-info align-content-center" role="alert">
                            Fitur ini digunakan untuk menaikkan semua siswa dari TP: <b><?= $tpBefore->tahun ?></b> ke
                            TP: <b><?= $tp_active->tahun ?></b>
                            <br>
                            <ul>
                                <li>
                                    Pilih kelas dari TP: <?= $tpBefore->tahun ?>
                                </li>
                                <li>
                                    Klik SIMPAN untuk menaikan siswa dalam kelas terpilih
                                </li>
                                <li>
                                    Siswa yang menurut rapor tidak naik maka otomatis tidak naik
                                </li>
                                <li>
                                    Untuk mengatur wali kelas baru gunakan menu <b>DATA UMUM -> GURU -> Jabatan</b>
                                </li>
                                <li>
                                    Untuk siswa kelas akhir diatur di menu <b>ALUMNI</b>
                                </li>
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 mb-2">
                                <label>Kelas <?= $tpBefore->tahun ?></label>
                                <select name="kelas_lama" id="opsi-kelas1" class="form-control">
                                    <?php foreach ($kelas_lama as $key => $kls):
                                        $selected = $key == $kelas_selected ? 'selected="selected"' : ''; ?>
                                        <option value="<?= $key ?>" <?= $selected ?>><?= $kls ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if (isset($siswas) && count($siswas) > 0 && count($kelases) > 0): ?>
                                <div class="col-sm-8 mb-2 text-end">
                                    <label>Mode Kenaikan</label>
                                    <br>
                                    <div id="selector" class="btn-group mb-4"
                                        data-active="<?= $type_selected ?? 'persiswa'; ?>">
                                        <button type="button" class="btn active btn-success"
                                            data-bs-target="persiswa"><b>Persiswa</b></button>
                                        <button type="button" class="btn btn-success"
                                            data-bs-target="perkelas"><b>Perkelas</b></button>
                                        <button type="button" class="btn btn-success" data-bs-target="allkelas"><b>Semua
                                                Kelas</b></button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (count($kelas_lama) == 0): ?>
                                <div class="col-12 alert alert-default-danger align-content-center" role="alert">
                                    Tidak ada kelas baru di Tahun Pelajaran sebelumnya
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                        if (isset($kelas_selected)): ?>
                            <?php if (count($siswas) > 0): ?>
                                <div class="row">
                                    <?php if (isset($level) && count($kelases) == 0): ?>
                                        <div class="col-12 alert alert-default-danger align-content-center" role="alert">
                                            Tidak ada kelas di Tahun Pelajaran sebelumnya.
                                            <!--
                                        <div class="text-end">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDialog"><i class="bi bi-plus"></i><span class="ms-1">Buat Kelas Baru</span>
                                            </button>
                                        </div>
                                        -->
                                        </div>
                                    <?php else: ?>
                                        <div class="col-12 col-tab" id="persiswa">
                                            <table class="table table-striped table-bordered table-hover" id="tbl-siswa">
                                                <thead class="alert alert-default-primary">
                                                    <tr>
                                                        <th width="50" height="50" class="text-center p-0 align-middle">No.</th>
                                                        <th class="text-center p-0 align-middle">N I S N</th>
                                                        <th class="text-center p-0 align-middle">N I S</th>
                                                        <th class="text-center p-0 align-middle p-0">Nama Siswa</th>
                                                        <th class="text-center p-0 align-middle">Kelas Lama</th>
                                                        <th class="text-center p-0 align-middle">Naik/Tdk. Naik</th>
                                                        <th class="text-center p-0 align-middle">
                                                            <select class="form-control" id="opsi-all">
                                                                <option value="0">Pilih Kelas Baru</option>
                                                                <?php foreach ($kelases as $key => $kls): ?>
                                                                    <option value="<?= $key ?>"><?= $kls ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $no = 1;
                                                    foreach ($siswas as $siswa):
                                                        $siswa_di_kelas_baru = $siswa_kelas_baru[$siswa->id_siswa] ?? null;
                                                        $kode_naik = $siswa->naik != null ? $siswa->naik : '1';
                                                        ?>
                                                        <tr>
                                                            <td class="d-none id-siswa align-middle"><?= $siswa->id_siswa ?></td>
                                                            <td class="d-none id-naik align-middle"><?= $kode_naik ?></td>
                                                            <td class="text-center align-middle"><?= $no ?></td>
                                                            <td class="text-center align-middle"><?= $siswa->nisn ?></td>
                                                            <td class="text-center align-middle"><?= $siswa->nis ?></td>
                                                            <td class="align-middle"><?= $siswa->nama ?></td>
                                                            <td class="text-center align-middle"><?= $siswa->nama_kelas ?></td>
                                                            <td class="text-center align-middle">
                                                                <?= $siswa->naik != null && $siswa->naik == '0' ? 'Tdk Naik' : 'Naik' ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($siswa->naik != null && $siswa->naik == '0'): ?>
                                                                    <select class="form-control" data-name="kelas-baru" disabled
                                                                        readonly="readonly">
                                                                        <option value="<?= $siswa->id_kelas ?>">Tetap di
                                                                            kelas <?= $siswa->nama_kelas ?></option>
                                                                    </select>
                                                                <?php else: ?>
                                                                    <select class="form-control opsi-kls" data-name="kelas-baru">
                                                                        <option value="0">Pilih Kelas Baru</option>
                                                                        <?php foreach ($kelases as $key => $kls):
                                                                            $selected_new = $siswa_di_kelas_baru && $siswa_di_kelas_baru->nama_kelas == $kls ? 'selected' : '' ?>
                                                                            <option value="<?= $key ?>" <?= $selected_new ?>><?= $kls ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php $no++; endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-12 col-tab mb-2 d-none" id="perkelas">
                                            <div class="alert alert-default-success align-content-center" role="alert">
                                                <ul class="m-0">
                                                    <li>
                                                        Untuk menaikkan secara otomatis ke <b>Kelas Baru yang dipilih di bawah.</b>.
                                                    </li>
                                                    <li>
                                                        Tindakan ini akan merubah semua kemaikan yang disimpan di <b>Tab PERSISWA</b>.
                                                    </li>
                                                </ul>
                                            </div>
                                            <label>Naik ke kelas:</label>
                                            <select id="select-kelas" class="form-control">
                                                <option value="0">Pilih Kelas Baru</option>
                                                <?php foreach ($kelases as $key => $kls): ?>
                                                    <option value="<?= $key ?>"><?= $kls ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-tab mb-2 d-none" id="allkelas">
                                            <div class="alert alert-default-warning align-content-center" role="alert">
                                                <ul class="m-0">
                                                    <li>
                                                        Tabel ini adalah untuk menaikkan secara otomatis.
                                                    </li>
                                                    <li>
                                                        Jika kelas belum ada tanda <span class="text-success"><i
                                                                class="bi bi-check"></i></span>,
                                                        mungkin saja sudah dinaikkan ke kelas lain. Periksa kembali <b>Tab PERSISWA</b>.
                                                    </li>
                                                </ul>
                                            </div>

                                            <?= form_open('', array('id' => 'naikkanallkelas')) ?>
                                            <table class="table table-bordered table-striped table-sm" id="tbl-allkelas">
                                                <thead class="alert alert-default-primary">
                                                    <tr>
                                                        <th class="text-center align-middle">No.</th>
                                                        <th class="text-center align-middle">TP <?= $tpBefore->tahun ?><br />Kelas
                                                            sebelumnya</th>
                                                        <th class="text-center align-middle">TP <?= $tp_active->tahun ?><br />Naik ke
                                                            kelas</th>
                                                        <th class="text-center align-middle">Naik<br />
                                                            <input class="check my-checkbox" type="checkbox" id="check-all-kelas">
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody id="table-allkelas">
                                                    <?php
                                                    $nop = 1;
                                                    foreach ($kelas_lama as $key => $kls): ?>
                                                        <tr>
                                                            <td class="text-center align-middle">
                                                                <?= $nop ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?= $kls ?>
                                                                (<?= count($siswa_kelas[$key] ?? []) ?> siswa)
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?= $all_kelas_baru[$key] ?>
                                                                (<?= count($siswa_kelas[$all_kelas_baru[$key]] ?? []) ?> siswa)
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php if (count($siswa_kelas[$all_kelas_baru[$key]] ?? []) < count($siswa_kelas[$key] ?? [])): ?>
                                                                    <input type="checkbox" name="kelas_baru[<?= $key ?>]"
                                                                        value="<?= $all_kelas_baru[$key] ?>"
                                                                        class="check check-allkelas my-checkbox" />
                                                                <?php else: ?>
                                                                    <span class="text-success"><i class="bi bi-check"></i></span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $nop++;
                                                    endforeach; ?>
                                                </tbody>
                                            </table>
                                            <?= form_close(); ?>
                                        </div>
                                        <div class="col-12 mb-2">
                                            <ul class="alert alert-default-warning">
                                                <li>Siswa yang naik kelas otomatis akan dipindahkan ke kelas baru terpilih</li>
                                                <li>Siswa yang tidak naik akan tetap tinggal di kelas lama</li>
                                            </ul>
                                        </div>
                                        <div class="col-12 text-end">
                                            <?= form_open('', array('id' => 'naikkankelas')) ?>
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i><span
                                                    class="ms-1">Simpan</span>
                                            </button>
                                            <?= form_close(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="col-12 alert alert-default-warning align-content-center" role="alert">
                                    Tidak ada siswa dikelas ini
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div id="empty" class="col-12 alert alert-default-warning align-content-center" role="alert">
                                Silahkan pilih kelas
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-default-warning align-content-center" role="alert">
                            Tidak ada kelas di tahun sebelumnya
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    </div>

    <script>
        const klsSelected = '<?= $kelas_selected ?? ''; ?>';
        let typeSelected = '<?= $type_selected ?? 'persiswa'; ?>'
        let uncheckedKelas = [];
        let checkedKelas = [];

        let uncheckedPerKelas = [];
        let checkedPerKelas = [];
        $(document).ready(function () {
            const initialActiveTarget = $('#selector').data('active');
            function activateTab(targetId) {
                if (targetId === 'allkelas') {
                    $('#empty').addClass('d-none');
                } else {
                    if (!klsSelected) $('#empty').removeClass('d-none');
                }
                $('#selector button').removeClass('active');
                $('.col-tab[id]').addClass('d-none');

                $('#selector button[data-bs-target="' + targetId + '"]')
                    .addClass('active');

                $('#' + targetId).removeClass('d-none');
            }

            if (initialActiveTarget) {
                activateTab(initialActiveTarget);
            } else {
                activateTab($('#selector button').first().data('target'));
            }

            $('#selector button').click(function () {
                const targetSectionId = $(this).data('target');
                activateTab(targetSectionId); // Use the existing function for click events
                typeSelected = targetSectionId
            });
            $('#opsi-all').change(function () {
                $('.opsi-kls').val($(this).val())
            })

            var opsiKelas = $('#opsi-kelas1');
            var slctd = klsSelected == '' ? "selected='selected'" : "";
            opsiKelas.prepend("<option value='' " + slctd + " disabled='disabled'>Pilih Kelas</option>");

            opsiKelas.change(function () {
                const kls = $(this).val();
                window.location.href = `${base_url}datakelas/kenaikan?kelas=${kls}&type=${typeSelected}`;
            });

            $('#naikkankelas').submit(function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const form = $('#naikkankelas').serialize();
                const $rows = $('#tbl-siswa').find('tr'), headers = $rows.splice(0, 1);
                const idKlsLama = $('#opsi-kelas1').val();
                let jsonKelas = [];

                let mode;
                if (!$('#allkelas').is(':hidden')) {
                    mode = 'allkelas';
                    naikkanAllKelas();
                } else if (!$('#perkelas').is(':hidden')) {
                    mode = 'perkelas';
                    const opsiKlsBaru = $('#select-kelas'); // opsi perkelas
                    const namaKLsBaru = opsiKlsBaru.find("option:selected").text();
                    const $kelasBaru = $('#select-kelas').val(); // opsi perkelas

                    $rows.each((i, row) => {
                        const $idSiswa = $(row).find(".id-siswa").text();
                        const $naik = $(row).find(".id-naik").text();

                        let item = {};
                        if ($naik == '1') {
                            item["id_siswa"] = $idSiswa;
                            //item ["kelas_baru"] = $kelasBaru; // dari opsi perkelas
                            item['id_kls_lama'] = idKlsLama;
                            item["nama_kls_baru"] = namaKLsBaru;
                            jsonKelas.push(item);
                        } else {
                            const $colKelasBaru = $(row).find('select[data-name="kelas-baru"]'); // dari opsi persiswa
                            if ($colKelasBaru.val() != '0') {
                                item["id_siswa"] = $idSiswa;
                                //item ["kelas_baru"] = $colKelasBaru.val();
                                item['id_kls_lama'] = idKlsLama;
                                item["nama_kls_baru"] = $colKelasBaru.find("option:selected").text();
                                jsonKelas.push(item);
                            }
                        }
                    });

                    if ($kelasBaru == '0') {
                        showInfoToast('Isi KELAS BARU terlebih dahulu');
                        return;
                    }
                } else if (!$('#persiswa').is(':hidden')) {
                    //persiswa
                    mode = 'persiswa';
                    $rows.each((i, row) => {
                        const $idSiswa = $(row).find(".id-siswa").text();
                        const $colKelasBaru = $(row).find('select[data-name="kelas-baru"]');
                        const $kelasBaru = $colKelasBaru.val();

                        if ($kelasBaru != "0") {
                            let item = {};
                            //item ["kelas_baru"] = $kelasBaru;
                            item["id_siswa"] = $idSiswa;
                            item["nama_kls_baru"] = $colKelasBaru.find("option:selected").text();
                            item['id_kls_lama'] = idKlsLama;
                            jsonKelas.push(item);
                        }
                    });
                }
                console.log(mode)

                if (mode !== 'allkelas') {
                    const dataPost = form + '&mode=' + mode + '&kelas=' + JSON.stringify(jsonKelas);
                    console.log(dataPost);

                    if (jsonKelas.length > 0) {
                        Swal.fire({
                            title: "Menaikan siswa",
                            text: "Silahkan tunggu....",
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: base_url + 'datakelas/naikkelas',
                            type: "POST",
                            data: dataPost,
                            success: function (data) {
                                console.log('response', data);
                                if (data) {
                                    Swal.fire({
                                        title: "Sukses",
                                        text: "Data berhasil disimpan",
                                        icon: "success",
                                        showCancelButton: false,
                                    }).then(result => {
                                        window.location.href = `${base_url}datakelas/kenaikan?kelas=${klsSelected}&type=${typeSelected}`;
                                        //console.log(data);
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Gagal",
                                        text: "Data Tidak Tersimpan",
                                        icon: "error",
                                        showCancelButton: false,
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log(xhr.responseText);
                                Swal.fire({
                                    title: "Error",
                                    text: xhr.responseText,
                                    icon: "error"
                                });
                            }
                        });
                    } else {
                        if (klsSelected) showInfoToast('Tidak ada data yg dipilih')
                        else showInfoToast('Pilih kelas terlebih dulu')
                    }
                }
            });

            $("#check-all").prop("checked", false);
            $('#check-all').prop('indeterminate', false)
            $("#check-all").on("click", function () {
                const count = $('#table-body .check-kelas').length;
                console.log('check', count)
                if (count > 0) {
                    if (this.checked) {
                        $(".check-kelas").each(function () {
                            this.checked = true;
                        });
                    } else {
                        $(".check-kelas").each(function () {
                            this.checked = false;
                        });
                    }
                    findUnchecked()
                }
            });

            $("#table-body").on("change", ".check-kelas", function () {
                console.log('change')
                findUnchecked();
            });
            $('#managekelas').submit(function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                Swal.fire({
                    title: "Salin Kelas",
                    text: "Kelas yang dipilih akan dicopy ke SMT II",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Copy"
                }).then(result => {
                    if (result.value) {
                        $.ajax({
                            url: base_url + 'datakelas/copyallfromsmt1',
                            type: "POST",
                            data: $(this).serialize(),
                            success: function (data) {
                                console.log(data);
                                Swal.fire({
                                    title: "Berhasil",
                                    text: "Kelas berhasil disalin",
                                    icon: "success"
                                }).then(result => {
                                    if (result.value) {
                                        window.location.href = `${base_url}datakelas/kenaikan?kelas=${klsSelected}&type=${typeSelected}`;
                                    }
                                });
                            },
                            error: function (xhr, status, error) {
                                console.log(xhr.responseText);
                                Swal.fire({
                                    title: "Gagal",
                                    text: JSON.stringify(xhr),
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });


            //allkelas
            $("#check-all-kelas").prop("checked", false);
            $('#check-all-kelas').prop('indeterminate', false)
            $("#check-all-kelas").on("click", function () {
                const count = $('#table-allkelas .check-allkelas').length;
                console.log('check', count)
                if (count > 0) {
                    if (this.checked) {
                        $(".check-allkelas").each(function () {
                            this.checked = true;
                        });
                    } else {
                        $(".check-allkelas").each(function () {
                            this.checked = false;
                        });
                    }
                    findUncheckedPerkelas()
                }
            });

            $("#table-allkelas").on("change", ".check-allkelas", function () {
                console.log('change')
                findUncheckedPerkelas();
            });
        });

        function naikkanAllKelas() {
            if (uncheckedPerKelas.length === 0 && checkedPerKelas.length === 0) {
                showInfoToast('Tidak ada data yg dipilih')
                return;
            }
            const form = $('#naikkanallkelas').serialize();
            Swal.fire({
                title: "Naikkan semua Siswa",
                text: "Kelas yang dipilih akan dinaikkan ke kelas baru",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: "Naikkan"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'datakelas/naikkan_semua',
                        type: "POST",
                        data: form,
                        success: function (data) {
                            console.log(data);
                            Swal.fire({
                                title: "Berhasil",
                                text: data.res + "siswa berhasil disimpan",
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.href = `${base_url}datakelas/kenaikan?kelas=${klsSelected}&type=${typeSelected}`;
                                }
                            });
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            Swal.fire({
                                title: "Gagal",
                                text: JSON.stringify(xhr),
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

        function findUnchecked() {
            uncheckedKelas = [];
            checkedKelas = [];
            const count = $('#table-body tr:visible .check-kelas').length;

            $("#table-body tr:visible .check-kelas:not(:checked)").each(function () {
                uncheckedKelas.push($(this).val());
            });
            $("#table-body tr:visible .check-kelas:checked").each(function () {
                checkedKelas.push($(this).val());
            });
            const countChecked = $("#table-body tr:visible .check-kelas:checked").length;

            $("#check-all").prop({
                checked: countChecked === count,
                indeterminate: countChecked > 0 && countChecked < count
            });

            console.log('checked', checkedKelas.length, uncheckedKelas.length)
            $("#save-all").attr('disabled', countChecked === 0);
        }

        function findUncheckedPerkelas() {
            uncheckedPerKelas = [];
            checkedPerKelas = [];
            const count = $('#table-allkelas tr:visible .check-allkelas').length;

            $("#table-allkelas tr:visible .check-allkelas:not(:checked)").each(function () {
                uncheckedPerKelas.push($(this).val());
            });
            $("#table-allkelas tr:visible .check-allkelas:checked").each(function () {
                checkedPerKelas.push($(this).val());
            });
            const countChecked = $("#table-allkelas tr:visible .check-allkelas:checked").length;

            $("#check-all-kelas").prop({
                checked: countChecked === count,
                indeterminate: countChecked > 0 && countChecked < count
            });

            console.log('checked', checkedPerKelas.length, uncheckedPerKelas.length)
        }
    </script>