<?php
/**

 * Date: 21/10/2020
 * Time: 17:56
 */
?>

<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $judul ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6><?= $subjudul ?></h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tabel</th>
                                    <th>Row</th>
                                    <th>Convert</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables as $table => $count): ?>
                                    <tr>
                                        <td><?= $table ?></td>
                                        <td><?= $count ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm btn-convert"
                                                onclick="convertTable('<?= $table ?>', <?= $count ?>)">Convert</button>
                                        </td>
                                        <td>
                                            <span class="d-none" id="progress_<?= $table ?>">
                                                <i class="bi bi-spinner fa-spin me-2"></i>
                                            </span>
                                            <span id="text_<?= $table ?>"></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <small><i class="alert-default-danger"> Menu ini untuk konversi gambar Base64 pada database ke
                                folder uploads (Bank Soal)</i></small>
                        <?= form_open('', array('id' => 'convert')); ?><?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <script>
        $(document).ready(function () {
            ajaxcsrf()
        });

        function convertTable(table, count) {
            const limit = 50; // Jumlah data per halaman
            const totalPages = Math.ceil(count / limit);
            const delay = 300;
            console.log(`Mengirim data tabel '${table}' dalam ${totalPages} halaman.`);

            let page = 1;
            const btn = $('.btn-convert');
            const prg = $('#progress_' + table);
            const txt = $('#text_' + table);
            function sendPageData() {
                if (page > totalPages) {
                    console.log("Semua data telah dikirim.");
                    btn.prop('disabled', false)
                    prg.addClass('d-none')
                    return;
                }

                const percent = (page / totalPages) * 100;
                prg.removeClass('d-none');
                btn.prop('disabled', true)
                txt.text(`${percent.toFixed(0)}%`);

                const offset = (page - 1) * limit;

                let formActivate = new FormData($('#convert')[0])
                formActivate.append('table', table)
                formActivate.append('limit', limit)
                formActivate.append('offset', offset)

                $.ajax({
                    url: base_url + 'dbconvert/convert',
                    type: 'POST',
                    processData: false,
                    contentType: false,
                    data: formActivate,
                    success: function (response) {
                        console.log(`Halaman ${page} berhasil dikirim.`, response);
                        page++;
                        //sendPageData(); // Kirim halaman berikutnya
                        setTimeout(sendPageData, delay);
                    },
                    error: function (xhr, status, error) {
                        console.error(`Gagal mengirim halaman ${page}:`, error);
                    }
                });
            }

            sendPageData(); // Mulai dari halaman pertama
        }
    </script>