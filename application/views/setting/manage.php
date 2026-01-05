<?php
/**

 * Date: 21/10/2020
 * Time: 17:56
 */
?>

<main class="app-main bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6><?= $subjudul ?></h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <strong>PENTING!</strong> <br>Pastikan Anda telah melakukan <b>BACKUP DATABASE</b> sebelum
                            menghapus data apapun. Penghapusan data bersifat permanen dan tidak dapat dikembalikan tanpa
                            backup.
                        </div>
                    </div>
                    <?php
                    $titles = [
                        '1' => 'Data Master & Konfigurasi',
                        '2' => 'Data Transaksional & Periodik',
                        '3' => 'Data Usang / Tidak Digunakan'
                    ];

                    foreach ($tables as $ket => $table):
                        if ($ket == '1') {
                            $keterangan = 'Data ini digunakan secara berkelanjutan (Jangka Panjang). Menghapus data ini dapat mempengaruhi operasional sistem.';
                            $badge = 'bg-primary';
                        } elseif ($ket == '2') {
                            $keterangan = 'Data yang terikat dengan Tahun Ajaran atau Semester. Aman dihapus jika sudah berganti tahun ajaran (Otomatis dibackup).';
                            $badge = 'bg-info';
                        } else {
                            $keterangan = 'Tabel dari versi sebelumnya atau modul yang sudah tidak aktif. Aman untuk dihapus.';
                            $badge = 'bg-secondary';
                        }
                        ?>
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="badge <?= $badge ?> p-2 rounded-circle me-2 d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-database"></i>
                                </div>
                                <h5 class="mb-0 fw-bold text-dark"><?= $titles[$ket] ?? 'Data Lainnya' ?></h5>
                            </div>

                            <div class="callout callout-info border-info bg-light mb-3 shadow-sm rounded">
                                <p class="mb-0 text-secondary small"><i class="bi bi-info-circle me-1 text-info"></i>
                                    <?= $keterangan ?></p>
                            </div>

                            <div class="table-responsive rounded shadow-sm border">
                                <table class="table table-hover table-striped w-100 mb-0">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th width="50" class="text-center py-3 align-middle rounded-start">No.</th>
                                            <th class="py-3 align-middle">Nama Tabel</th>
                                            <th class="text-center py-3 align-middle">Total Rows</th>
                                            <th class="text-center py-3 align-middle rounded-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($table as $info): ?>
                                            <tr>
                                                <td class="text-center align-middle"><?= $no ?></td>
                                                <td class="align-middle fw-medium text-dark"><?= $info['table'] ?>
                                                    <!-- <small class="d-block text-muted"><?= $info['name'] ?></small> -->
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="badge bg-light text-dark border shadow-sm"><?= $info['size'] ?>
                                                        Data</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php
                                                    $disable = $info['size'] == 0 ? 'disabled="disabled"' : '';
                                                    ?>
                                                    <button class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm"
                                                        onclick="hapus('<?= $info['table'] ?>')" <?= $disable ?>>
                                                        <i class="bi bi-trash-alt me-1"></i> Kosongkan
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php $no++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    </div>

    <?= form_open('', array('id' => 'delete-table')) ?>
    <?= form_close() ?>

    <script>
        function hapus(src) {
            Swal.fire({
                title: "Anda yakin?",
                html: "Tabel akan dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(result => {
                if (result.value) {
                    Swal.fire({
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                                Swal.showLoading();
                            }
                    });
                    $.ajax({
                        url: base_url + 'dbclear/hapustable',
                        type: "POST",
                        data: $('#delete-table').serialize() + '&table=' + src,
                        success: function (respon) {
                            console.log(respon);
                            Swal.fire({
                                title: "Berhasil",
                                text: respon.message,
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.href = base_url + 'dbclear';
                                }
                            });
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            const err = JSON.parse(xhr.responseText)
                            Swal.fire({
                                title: "Error",
                                text: err.Message,
                                icon: "error"
                            });
                        }
                    });
                }
            })
        }
    </script>