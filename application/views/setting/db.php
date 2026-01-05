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
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <button class="btn btn-primary me-2" onclick="processBackup()">BACKUP SEMUA</button>
                            <button class="btn btn-info me-2" onclick="processBackupDb()">BACKUP DB SAJA</button>
                            <button class="btn btn-success" onclick="processBackupFile()">BACKUP FILE SAJA</button>
                        </div>
                        <button class="btn btn-danger" onclick="hapusBackup()" <?= count($list) == 0 ? 'disabled' : '' ?>>HAPUS SEMUA</button>
                    </div>
                    <div class="progress" style="height: 30px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width:0%; height: 30px">
                        </div>
                    </div>
                   <p>1. Jangan merefresh/menutup halaman ini atau ke halaman lain selama proses backup berlangsung
                        <br>2. Jika backup gagal, gunakan cpanel/phpmyadmin untuk membackup file dan database
                        <br>3. Untuk Backup File (Gambar, Foto, dan lain sebagainya) Backup manual Folder Uploads
                        <br><span class="text-danger fw-bold">PENTING:</span> File Backup dienkripsi menggunakan system key. Pastikan Anda tidak mengganti encryption_key di config.php.
                    </p>
                    <hr>
                    <br>

                    <h6><b>RIWAYAT BACKUP</b></h6>
                    <?= form_open('', array('id' => 'edittp')) ?>
                    <table id="database" class="table table-striped table-bordered table-hover">
                        <thead class="alert-default-primary">
                        <tr>
                            <th width="50" height="50" class="text-center p-0 align-middle">No.</th>
                            <th class="text-center p-0 align-middle">Database/File</th>
                            <th class="text-center p-0 align-middle">Size</th>
                            <th class="text-center p-0 align-middle p-0">Tanggal Backup</th>
                            <th class="text-center p-0 align-middle">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        //sort($list);
                        usort($list, function ($a, $b) {
                            return $b['tgl'] <=> $a['tgl'];
                        });
                        foreach ($list as $key => $value) :?>
                            <tr>
                                <td class="text-center">
                                    <?= ($key + 1) ?>
                                </td>
                                <td class="text-center">
                                    <?= $value['nama'] . '.' . $value['type'] ?>
                                </td>
                                <td class="text-center">
                                    <?= $value['size'] ?>
                                </td>
                                <td class="text-center">
                                    <?= buat_tanggal(date('D, d M Y H:i', $value['tgl'])) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('dbmanager/download/' . $value['src']) ?>"
                                       download="<?= $value['src'] ?>" type="button"
                                       class="btn btn-xs btn-warning btn-download">Download</a>
                                    <button onclick="restore('<?= $value['src'] ?>')" type="button"
                                            class="btn btn-xs btn-success btn-restore">Restore
                                    </button>
                                    <button onclick="hapus('<?= $value['src'] ?>')" type="button"
                                            class="btn btn-xs btn-danger btn-hapus">Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

    function updateProgress(count, message) {
        console.log(count, message);
        var progress = $('.progress-bar');
        progress.css('width', `${Number(count)}%`).attr('aria-valuenow', `${count}`).text(`${count}%  ${message}`);
    }

    function processBackupDb() {
        Swal.fire({
            title: "Backup Database",
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                                Swal.showLoading();
                            }
        });

        updateProgress(10, 'Memulai Backup Database...');
        var data = {};
        data[CSRF_NAME] = CSRF_HASH;

        $.ajax({
            type: "POST",
            url: base_url + 'dbmanager/backupdb',
            data: data,
            success: function (response) {
                console.log(response);
                if(response.csrf_hash) CSRF_HASH = response.csrf_hash;
                updateProgress(100, response.message);
                Swal.fire({
                    title: "Berhasil",
                    text: "Database berhasil dibackup",
                    icon: "success"
                }).then(result => {
                    if (result.value) {
                        window.location.reload();
                    }
                })
            },
            error: function(xhr) {
                Swal.fire("Gagal", "Terjadi kesalahan: " + xhr.statusText, "error");
            }
        });
    }

    function processBackupFile() {
        Swal.fire({
            title: "Backup File Uploads",
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                                Swal.showLoading();
                            }
        });

        updateProgress(10, 'Memulai Backup File...');
        var data = {};
        data[CSRF_NAME] = CSRF_HASH;

        $.ajax({
            type: "POST",
            url: base_url + 'dbmanager/backupdata',
            data: data,
            success: function (response) {
                console.log(response);
                if(response.csrf_hash) CSRF_HASH = response.csrf_hash;
                updateProgress(100, response.message);
                Swal.fire({
                    title: "Berhasil",
                    text: "File Uploads berhasil dibackup",
                    icon: "success"
                }).then(result => {
                    if (result.value) {
                        window.location.reload();
                    }
                })
            },
            error: function(xhr) {
                Swal.fire("Gagal", "Terjadi kesalahan: " + xhr.statusText, "error");
            }
        });
    }

    function processBackup() {
        Swal.fire({
            title: "Backup database dan file sedang berjalan",
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                                Swal.showLoading();
                            }
        });

        updateProgress(5, ' ');
        var data = {};
        data[CSRF_NAME] = CSRF_HASH;

        $.ajax({
            type: "POST",
            url: base_url + 'dbmanager/backupdb',
            data: data,
            success: function (response) {
                console.log(response);
                if(response.csrf_hash) CSRF_HASH = response.csrf_hash; // Update token!
                updateProgress(50, response.message);
                backupData(); // Call next step
            },
            error: function(xhr) {
                Swal.fire("Gagal", "Terjadi kesalahan: " + xhr.statusText, "error");
            }
        });
    }

    function hapusBackup() {
        Swal.fire({
            title: "HAPUS SEMUA",
            html: "<span class='text-danger'><b>Semua backup akan dihapus dan tidak bisa dikembalikan!</b></span>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "YA, Hapus!"
        }).then(result => {
            if (result.value) {
                Swal.fire({
                    title: "Menghapus backup",
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
                            }
                });

                var data = {};
                data[CSRF_NAME] = CSRF_HASH;

                $.ajax({
                    url: base_url + 'dbmanager/del_backup',
                    type: "POST",
                    data: data,
                    success: function (respon) {
                        console.log(respon);
                        if(respon.csrf_hash) CSRF_HASH = respon.csrf_hash;

                        if (respon.status) {
                            Swal.fire({
                                title: "Berhasil",
                                text: respon.message,
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.href = base_url + 'dbmanager';
                                }
                            })
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                text: respon.message,
                                icon: "error"
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        Swal.fire({
                            title: "Error",
                            text: "Terjadi kesalahan dalam menghapus",
                            icon: "error"
                        });
                    }
                });

            }
        });
    }

    function backupData() {
        var data = {};
        data[CSRF_NAME] = CSRF_HASH;

        $.ajax({
            type: "POST",
            url: base_url + 'dbmanager/backupdata',
            data: data,
            success: function (response) {
                console.log(response);
                if(response.csrf_hash) CSRF_HASH = response.csrf_hash;

                updateProgress(100, response.message);
                Swal.fire({
                    title: "Berhasil",
                    text: "Semua file data berhasil dibackup",
                    icon: "success"
                }).then(result => {
                    if (result.value) {
                        window.location.href = base_url + 'dbmanager';
                    }
                })
            },
            error: function(xhr) {
                Swal.fire("Gagal", "Terjadi kesalahan: " + xhr.statusText, "error");
            }
        });
    }

    function hapus(src) {
        Swal.fire({
            title: "Anda yakin?",
            html: "File <b>" + src + "</b> akan dihapus!",
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

                var data = { src: src };
                data[CSRF_NAME] = CSRF_HASH;

                $.ajax({
                    url: base_url + 'dbmanager/hapusbackup',
                    type: "POST",
                    data: data,
                    success: function (respon) {
                        console.log(respon);
                        if(respon.csrf_hash) CSRF_HASH = respon.csrf_hash;

                        if (respon.status) {
                            Swal.fire({
                                title: "Berhasil",
                                text: respon.message,
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.href = base_url + 'dbmanager';
                                }
                            })
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                text: respon.message,
                                icon: "error"
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        Swal.fire({
                            title: "Error",
                            text: "Terjadi kesalahan saat menghapus",
                            icon: "error"
                        });
                    }
                });
            }
        })
    }

    function restore(src) {
        Swal.fire({
            title: "RESTORE BACKUP",
            html: "Anda yakin ingin merestore file <b>" + src + "</b>?<br><br><span class='text-danger'><b>PERINGATAN:</b><br>1. Data aplikasi saat ini akan DITIMPA/DIHAPUS.<br>2. Pastikan Anda sudah melakukan BACKUP terlebih dahulu.<br>3. Proses ini tidak dapat dibatalkan.</span>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "YA, Restore Data!"
        }).then(result => {
            if (result.value) {
                Swal.fire({
                    title: "Memproses Restore",
                    text: "Mohon tunggu, jangan tutup halaman ini...",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
                            }
                });
                
                var data = { src: src };
                data[CSRF_NAME] = CSRF_HASH;

                $.ajax({
                    url: base_url + 'dbmanager/restore',
                    type: "POST",
                    data: data,
                    success: function (respon) {
                        console.log(respon);
                        if(respon.csrf_hash) CSRF_HASH = respon.csrf_hash;
                        
                        if (respon.status) {
                            Swal.fire({
                                title: "Berhasil",
                                text: respon.message,
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            })
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                text: respon.message,
                                icon: "error"
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        Swal.fire({
                            title: "Error",
                            text: "Terjadi kesalahan server saat proses restore.",
                            icon: "error"
                        });
                    }
                });
            }
        })
    }

    $(document).ready(function () {
        var progress = $('.progress-bar');
        progress.css('width', `0%`).attr('aria-valuenow', `0`).text(``);
    });

</script>