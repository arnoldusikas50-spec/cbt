<?php


?>





<main class="app-main bg-light">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h3><?= $judul ?></h3>
                </div>
                <div class="col-6">
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-end">
                        <i class="bi bi-arrow-left-square"></i><span
                            class="d-none d-sm-inline-block ms-1">Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card card-primary card-outline shadow-sm mb-4">
                                <div class="card-header">
                                    <h5 class="card-title m-0"><i class="bi bi-person-lines-fill me-2"></i>Informasi
                                        Peserta & Ujian</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="symbol symbol-40px me-3">
                                                    <span
                                                        class="symbol-label bg-light-primary text-primary rounded-circle p-2">
                                                        <i class="bi bi-person fs-4"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-muted fs-7">Nama Peserta</div>
                                                    <div class="fw-bold text-dark"><?= $siswa->siswa->nama ?? '' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="symbol symbol-40px me-3">
                                                    <span
                                                        class="symbol-label bg-light-info text-info rounded-circle p-2">
                                                        <i class="bi bi-card-heading fs-4"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-muted fs-7">NIS / No. Peserta</div>
                                                    <div class="fw-bold text-dark">
                                                        <?= $siswa->siswa->nis ?? '-' ?> /
                                                        <?= $siswa->peserta->nomor_peserta ?? $siswa->no_peserta ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="symbol symbol-40px me-3">
                                                    <span
                                                        class="symbol-label bg-light-warning text-warning rounded-circle p-2">
                                                        <i class="bi bi-bank fs-4"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-muted fs-7">Kelas / Ruang / Sesi</div>
                                                    <div class="fw-bold text-dark">
                                                        <?= $siswa->kelas->nama_kelas ?? $siswa->kelas ?>
                                                    </div>
                                                    <small
                                                        class="text-muted"><?= $siswa->ruang_sesi->ruang->kode_ruang ?? '-' ?>
                                                        —
                                                        <?= $siswa->ruang_sesi->sesi->kode_sesi ?? $siswa->sesi->kode_sesi ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="symbol symbol-40px me-3">
                                                    <span
                                                        class="symbol-label bg-light-success text-success rounded-circle p-2">
                                                        <i class="bi bi-journal-text fs-4"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-muted fs-7">Mata Pelajaran</div>
                                                    <div class="fw-bold text-dark">
                                                        <?= $info->mapel->nama_mapel ?? $rekap_mapel ?>
                                                    </div>
                                                    <small
                                                        class="text-muted"><?= $info->guru->nama_guru ?? $rekap_guru ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <?php
                            $hanyaPG = count($soal_pg) > 0 && count($soal_kompleks) === 0 &&
                                count($soal_jodohkan) === 0 && count($soal_isian) === 0 && count($soal_esai) === 0 &&
                                count($soal_urutan) === 0 && count($soal_cloze) === 0;
                            ?>
                            <div class="card bg-gradient-primary shadow-sm mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-white opacity-75 small text-uppercase fw-bold">Nilai Akhir
                                            </div>
                                            <div class="fs-1 fw-bold text-white">
                                                <?= round($siswa->nilai_akhir->skor_total ?? 0, 2) ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="badge bg-white bg-opacity-25 text-white">
                                                <?= $hanyaPG ? 'PG Only (Otomatis)'
                                                    : (isset($siswa->nilai_akhir->dikoreksi) && $siswa->nilai_akhir->dikoreksi
                                                        ? '<i class="bi bi-check-circle-fill me-1"></i>Sudah Dikoreksi'
                                                        : '<i class="bi bi-exclamation-circle-fill me-1"></i>Belum Dikoreksi') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-body p-2">
                                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"
                                            title="Pilihan Ganda">
                                            PG: <?= round($siswa->nilai_akhir->skor_pg['skor_total'] ?? 0, 2) ?>
                                        </span>
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"
                                            title="PG Kompleks">
                                            PK: <?= round($siswa->nilai_akhir->skor_kompleks['skor_total'] ?? 0, 2) ?>
                                        </span>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"
                                            title="Menjodohkan">
                                            JO: <?= round($siswa->nilai_akhir->skor_jodohkan['skor_total'] ?? 0, 2) ?>
                                        </span>
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25"
                                            title="Isian Singkat">
                                            IS: <?= round($siswa->nilai_akhir->skor_isian['skor_total'] ?? 0, 2) ?>
                                        </span>
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"
                                            title="Uraian">
                                            ES: <?= round($siswa->nilai_akhir->skor_essai['skor_total'] ?? 0, 2) ?>
                                        </span>
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"
                                            title="Urutan">
                                            UR: <?= round($siswa->nilai_akhir->skor_urutan['skor_total'] ?? 0, 2) ?>
                                        </span>
                                        <span
                                            class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25"
                                            title="Isian Rumpang">
                                            CL: <?= round($siswa->nilai_akhir->skor_cloze['skor_total'] ?? 0, 2) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <?php if (!$hanyaPG): ?>
                                <button class="btn btn-success w-100 mt-3 py-2 shadow-sm fw-bold" id="btn-marked">
                                    <i class="bi bi-check2-all me-2"></i>Tandai Selesai Koreksi
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="konten-jawaban"></div>
        </div>
    </section>
    </div>

    <?= form_open('', array('id' => 'koreksi')) ?>
    <input type="hidden" name="is_rekap" value="<?= $is_rekap ?? '0' ?>">
    <input type="hidden" name="siswa" value="<?= $siswa->siswa_id ?? $siswa->id_siswa ?>">
    <input type="hidden" name="jadwal" value="<?= $id_jadwal ?>">
    <input type="hidden" name="bank" value="<?= $id_bank ?>">
    <?= form_close() ?>

    <script>
        $(document).ready(function () {
            setTimeout(function () {
                getKoreksi()
            }, 300)
        })

        async function ajaxPost(url, formData) {
            return new Promise((resolve, reject) => {
                setTimeout(function () {
                    $.ajax({
                        url: base_url + url,
                        method: "POST",
                        processData: false,
                        contentType: false,
                        data: formData,
                        success: function (respon) {
                            if (respon.success) {
                                resolve(respon); // Resolusi Promise ketika sukses
                            } else {
                                reject(respon.message); // Rejeksi Promise ketika ada error
                            }
                        },
                        error: function (xhr, status, error) {
                            reject(error); // Rejeksi Promise ketika ada error
                        }
                    });
                }, 200)
            });
        }

        async function getKoreksi() {
            let formData = new FormData($('#koreksi')[0]);
            const response = await ajaxPost('cbtnilai/get_koreksi', formData)
            console.log('resp', response)
            populateSoalPg(response.soal_pg, response.siswa.nilai_akhir.skor_pg.detail, response.info)
            populateSoalKompleks(response.soal_kompleks, response.siswa.nilai_akhir.skor_kompleks, response.info)
            populateSoalJodohkan(response.soal_jodohkan, response.siswa.nilai_akhir.skor_jodohkan, response.info)
            populateSoalIsian(response.soal_isian, response.siswa.nilai_akhir.skor_isian, response.info)
            populateSoalEsai(response.soal_esai, response.siswa.nilai_akhir.skor_essai, response.info)
            populateSoalUrutan(response.soal_urutan, response.siswa.nilai_akhir.skor_urutan, response.info)
            populateSoalCloze(response.soal_cloze, response.siswa.nilai_akhir.skor_cloze, response.info)


            $('#btn-marked').on('click', function () {
                $(this).attr('disabled', 'disabled');
                var dataPost = $('#koreksi').serialize();
                console.log(dataPost)
                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
                            }
                });
                $.ajax({
                    url: base_url + "cbtnilai/tandaikoreksi",
                    type: "POST",
                    data: dataPost,
                    success: function (data) {
                        $('#btn-marked').removeAttr('disabled');
                        console.log(data);
                        if (data.success) {
                            Swal.fire({
                                title: "Berhasil",
                                text: "Jawaban berhasil ditandai",
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                text: 'Tidak bisa menandai',
                                icon: "error"
                            });
                        }
                    }, error: function (xhr, status, error) {
                        $('#btn-marked').removeAttr('disabled');
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        Swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            })
        }

        function populateSoalPg(soalPg, detailsPg, info) {
            if (soalPg && soalPg.length > 0 && info.bobot_pg > 0) {
                const container = document.createElement("div");
                container.className = "card card-primary card-outline shadow-sm mb-4 collapsed-card";

                // Card Header
                const header = document.createElement("div");
                header.className = "card-header";

                const title = document.createElement("h6");
                title.className = "card-title";
                title.innerHTML = "<b>I. PILIHAN GANDA</b>";

                const tools = document.createElement("div");
                tools.className = "card-tools";

                const button = document.createElement("button");
                button.type = "button";
                button.className = "btn btn-tool";
                // button.dataset.cardWidget = "collapse";
                button.title = "Collapse";
                button.onclick = function () {
                    const card = $(this).closest('.card');
                    const body = card.find('.card-body');
                    const icon = $(this).find('i');
                    if (card.hasClass('collapsed-card')) {
                        card.removeClass('collapsed-card');
                        body.slideDown();
                        icon.removeClass('bi-plus').addClass('bi-dash');
                    } else {
                        card.addClass('collapsed-card');
                        body.slideUp();
                        icon.removeClass('bi-dash').addClass('bi-plus');
                    }
                };

                const icon = document.createElement("i");
                icon.className = "bi bi-plus";

                button.appendChild(icon);
                tools.appendChild(button);
                header.appendChild(title);
                header.appendChild(tools);
                container.appendChild(header);

                // Card Body
                const body = document.createElement("div");
                body.className = "card-body";

                const alert = document.createElement("div");
                alert.className = "alert alert-light-primary border border-primary border-opacity-25 text-primary";
                alert.role = "alert";

                const ul = document.createElement("ul");

                const li1 = document.createElement("li");
                li1.innerHTML = `Bobot soal: <b>${info.bobot_pg}</b>. Jumlah soal: <b>${info.tampil_pg}</b>. Max point persoal: <b>${(info.bobot_pg / info.tampil_pg).toFixed(2)}</b>.`;
                ul.appendChild(li1);

                const li2 = document.createElement("li");
                li2.textContent = "Point soal PG tidak bisa diedit";
                ul.appendChild(li2);

                alert.appendChild(ul);
                body.appendChild(alert);

                // Table
                const tableContainer = document.createElement("div");
                tableContainer.className = "table-responsive";

                const table = document.createElement("table");
                table.id = "table-pg";
                table.className = "w-100 table table-striped table-bordered border-primary nowrap";

                const thead = document.createElement("thead");
                const headerRow = document.createElement("tr");

                const headers = [
                    "No. Soal",
                    "Soal",
                    "Pilihan",
                    "Jawaban Bank Soal",
                    "No. Acak",
                    "Pilihan Acak",
                    "Jawaban Siswa Acak",
                    "Jawaban Siswa",
                    "Analisa",
                    `Point Max. ${formatNilai(info.bobot_pg / info.tampil_pg)}`
                ];

                headers.forEach((text, index) => {
                    const th = document.createElement("th");
                    th.className = "text-center align-middle bg-light";
                    if (index >= 4 && index <= 6) th.classList.add("d-none");
                    th.textContent = text;
                    headerRow.appendChild(th);
                });

                thead.appendChild(headerRow);
                table.appendChild(thead);

                const tbody = document.createElement("tbody");
                let totalScore = 0;

                soalPg.forEach((soal, index) => {
                    const detail = detailsPg.find(d => d.id_soal === soal.id_soal) || {};
                    const row = document.createElement("tr");

                    // Nomor Soal
                    const cellNo = document.createElement("td");
                    cellNo.className = "text-center";
                    cellNo.textContent = index + 1;
                    row.appendChild(cellNo);

                    // Soal
                    const cellSoal = document.createElement("td");
                    cellSoal.innerHTML = optimizeImageSrc(soal?.soal);
                    row.appendChild(cellSoal);

                    // Pilihan
                    const cellPilihan = document.createElement("td");
                    const ol = document.createElement("ol");
                    ol.type = "A";

                    ["opsi_a", "opsi_b", "opsi_c", "opsi_d", "opsi_e"].forEach((key, i) => {
                        if (i >= info.opsi) return;
                        const li = document.createElement("li");
                        li.innerHTML = optimizeImageSrc(soal[key]);
                        ol.appendChild(li);
                    });

                    cellPilihan.appendChild(ol);
                    row.appendChild(cellPilihan);

                    // Jawaban Benar
                    const cellJawaban = document.createElement("td");
                    cellJawaban.className = "text-center";
                    cellJawaban.textContent = soal?.jawaban?.toUpperCase() || "";
                    row.appendChild(cellJawaban);

                    // Jawaban Siswa
                    const cellJawabanSiswa = document.createElement("td");
                    cellJawabanSiswa.className = "text-center";
                    cellJawabanSiswa.textContent = detail?.jawaban_siswa?.toUpperCase() || "";
                    row.appendChild(cellJawabanSiswa);

                    // Analisa
                    const cellAnalisa = document.createElement("td");
                    cellAnalisa.className = "text-center";
                    cellAnalisa.innerHTML = optimizeImageSrc(detail?.analisa) || "";
                    row.appendChild(cellAnalisa);

                    // Skor
                    const cellSkor = document.createElement("td");
                    cellSkor.className = "text-center";
                    cellSkor.textContent = detail?.skor_jawaban || "0";
                    totalScore += parseFloat(detail?.skor_jawaban) || 0;
                    row.appendChild(cellSkor);

                    tbody.appendChild(row);
                });

                const totalRow = document.createElement("tr");

                const totalCell = document.createElement("td");
                totalCell.colSpan = 6;
                totalCell.className = "text-end text-bold";
                totalCell.textContent = "TOTAL SCORE PILIHAN GANDA";

                const totalScoreCell = document.createElement("td");
                totalScoreCell.className = "bg-light text-center text-bold text-primary";
                totalScoreCell.textContent = formatNilai(totalScore)//totalScore.toFixed(2);

                totalRow.appendChild(totalCell);
                totalRow.appendChild(totalScoreCell);
                tbody.appendChild(totalRow);

                table.appendChild(tbody);
                tableContainer.appendChild(table);
                body.appendChild(tableContainer);
                container.appendChild(body);

                // Append the final result to a parent container
                document.getElementById("konten-jawaban").appendChild(container);
            }
        }

        function populateSoalKompleks(soalKompleks, detailsPg2, info) {
            if (soalKompleks && soalKompleks.length > 0 && info.bobot_kompleks > 0) {
                const container = document.createElement("div");
                container.className = "card card-info card-outline shadow-sm mb-4 collapsed-card";

                // Card Header
                const header = document.createElement("div");
                header.className = "card-header"; // Match Cyan Badge

                const title = document.createElement("h6");
                title.className = "card-title";
                title.innerHTML = "<b>II. PILIHAN GANDA KOMPLEKS</b>";

                const tools = document.createElement("div");
                tools.className = "card-tools";

                const button = document.createElement("button");
                button.type = "button";
                button.className = "btn btn-tool";
                // button.dataset.cardWidget = "collapse";
                button.title = "Collapse";
                button.onclick = function () {
                    const card = $(this).closest('.card');
                    const body = card.find('.card-body');
                    const icon = $(this).find('i');
                    if (card.hasClass('collapsed-card')) {
                        card.removeClass('collapsed-card');
                        body.slideDown();
                        icon.removeClass('bi-plus').addClass('bi-dash');
                    } else {
                        card.addClass('collapsed-card');
                        body.slideUp();
                        icon.removeClass('bi-dash').addClass('bi-plus');
                    }
                };

                const icon = document.createElement("i");
                icon.className = "bi bi-plus";

                button.appendChild(icon);
                tools.appendChild(button);
                header.appendChild(title);
                header.appendChild(tools);
                container.appendChild(header);

                // Card Body
                const body = document.createElement("div");
                body.className = "card-body";

                const alert = document.createElement("div");
                alert.className = "alert alert-light-info border border-info border-opacity-25 text-info";
                alert.role = "alert";

                const ul = document.createElement("ul");
                const infoList = [
                    `Bobot soal: <b>${info.bobot_kompleks}</b>. Jumlah soal: <b>${info.tampil_kompleks}</b>. Max point persoal: <b>${(info.bobot_kompleks / info.tampil_kompleks).toFixed(2)}</b>.`,
                    "Point soal PG Kompleks bisa diedit",
                    "Utamakan mengkoreksi hasil analisa yang berwarna kuning",
                    'Klik <i class="bi bi-pencil text-yellow"></i> untuk mengedit point. Klik <i class="bi bi-undo text-blue"></i> untuk mengembalikan ke point otomatis',
                    'Klik tombol <b>SIMPAN</b> untuk menyimpan perubahan point'
                ];

                infoList.forEach(text => {
                    const li = document.createElement("li");
                    li.innerHTML = text;
                    ul.appendChild(li);
                });

                alert.appendChild(ul);
                body.appendChild(alert);

                // Table
                const tableContainer = document.createElement("div");
                tableContainer.className = "table-responsive";

                const table = document.createElement("table");
                table.id = "table-pg2";
                table.className = "w-100 table table-striped table-bordered border-info nowrap";

                const thead = document.createElement("thead");
                const headerRow = document.createElement("tr");

                const headers = [
                    "No. Soal",
                    "Soal",
                    "Pilihan",
                    "Jawaban Bank Soal",
                    "Jawaban Siswa",
                    "Analisa",
                    `Point Max. ${formatNilai(info.bobot_kompleks / info.tampil_kompleks)}`,
                    "Aksi"
                ];

                headers.forEach((text, index) => {
                    const th = document.createElement("th");
                    if (index === 6) {
                        th.style.minWidth = '120px'
                    }
                    th.className = "text-center align-middle bg-light";
                    th.innerHTML = text;
                    headerRow.appendChild(th);
                });

                thead.appendChild(headerRow);
                table.appendChild(thead);

                const tbody = document.createElement("tbody");
                let totalScore = 0;

                soalKompleks.forEach((soal, index) => {
                    const detail = detailsPg2.detail.find(d => d.id_soal === soal.id_soal) || {};
                    const row = document.createElement("tr");

                    // Nomor Soal
                    const cellNo = document.createElement("td");
                    cellNo.className = "text-center";
                    cellNo.textContent = index + 1;
                    row.appendChild(cellNo);

                    // Soal
                    const cellSoal = document.createElement("td");
                    cellSoal.innerHTML = optimizeImageSrc(soal?.soal);
                    row.appendChild(cellSoal);

                    // Pilihan
                    const cellPilihan = document.createElement("td");
                    const ol = document.createElement("ol");
                    ol.type = "A";

                    for (const option in soal?.opsi_a) {
                        const li = document.createElement("li");
                        li.innerHTML = optimizeImageSrc(soal?.opsi_a[option]);
                        ol.appendChild(li);
                    }

                    cellPilihan.appendChild(ol);
                    row.appendChild(cellPilihan);

                    // Jawaban Benar
                    const cellJawaban = document.createElement("td");
                    cellJawaban.className = "text-center";
                    cellJawaban.textContent = soal?.jawaban?.map(j => j?.toUpperCase()).join(", ") || "";
                    row.appendChild(cellJawaban);

                    // Jawaban Siswa
                    const cellJawabanSiswa = document.createElement("td");
                    cellJawabanSiswa.className = "text-center";
                    cellJawabanSiswa.textContent = detail?.jawaban_siswa?.map(j => j.toUpperCase()).join(", ") || "";
                    row.appendChild(cellJawabanSiswa);

                    // Analisa
                    const cellAnalisa = document.createElement("td");
                    cellAnalisa.className = "text-center";
                    cellAnalisa.innerHTML = optimizeImageSrc(detail?.analisa) || "";
                    row.appendChild(cellAnalisa);

                    // Skor
                    const cellSkor = document.createElement("td");
                    cellSkor.className = "text-center";

                    const input = document.createElement("input");
                    input.id = `input${soal?.id_soal}`;
                    input.name = `input${soal?.id_soal}`;
                    input.type = "number";
                    input.min = "0";
                    input.max = (info.bobot_kompleks / info.tampil_kompleks).toFixed(2);
                    input.step = "0.1";
                    input.style.display = "none";
                    input.value = detail?.skor_jawaban || 0;

                    const span = document.createElement("span");
                    span.id = `span${soal?.id_soal}`;
                    span.className = "pg2";
                    span.dataset.idsoal = soal?.id_soal;
                    span.textContent = detail?.skor_jawaban || 0;

                    cellSkor.appendChild(input);
                    cellSkor.appendChild(span);
                    row.appendChild(cellSkor);

                    // Aksi
                    const cellAksi = document.createElement("td");

                    const editButton = document.createElement("button");
                    editButton.id = `edit${soal?.id_soal}`;
                    editButton.className = "btn btn-sm";
                    editButton.innerHTML = '<i class="bi bi-pencil text-yellow"></i>';
                    editButton.onclick = () => edit(soal?.id_soal, "pg2");

                    const undoButton = document.createElement("button");
                    undoButton.id = `undo${soal?.id_soal}`;
                    undoButton.className = "btn btn-sm";
                    undoButton.innerHTML = '<i class="bi bi-undo text-blue"></i>';
                    undoButton.onclick = () => undo(soal?.id_soal, detail?.nilai_asli);

                    cellAksi.appendChild(editButton);
                    cellAksi.appendChild(undoButton);
                    row.appendChild(cellAksi);

                    tbody.appendChild(row);

                    totalScore += parseFloat(detail?.skor_jawaban || 0);
                });

                const totalRow = document.createElement("tr");
                const totalCell = document.createElement("td");
                totalCell.colSpan = 6;
                totalCell.className = "text-end align-middle text-bold";
                totalCell.textContent = "TOTAL SCORE PILIHAN GANDA KOMPLEKS";

                const skorAsli = formatNilai(detailsPg2.skor_asli)
                const skorTotal = formatNilai(detailsPg2.skor_total)

                const totalScoreCell = document.createElement("td");
                totalScoreCell.className = "bg-light text-center text-bold align-middle text-primary";
                totalScoreCell.textContent = skorTotal

                const saveCell = document.createElement("td");
                saveCell.className = "align-middle";

                const saveButton = document.createElement("button");
                saveButton.id = "pg2";
                saveButton.className = "btn btn-sm btn-primary";
                saveButton.textContent = "Simpan";
                saveButton.onclick = () => simpan(saveButton);
                saveCell.appendChild(saveButton);

                totalRow.appendChild(totalCell);
                totalRow.appendChild(totalScoreCell);
                totalRow.appendChild(saveCell);
                tbody.appendChild(totalRow);

                if (skorAsli !== skorTotal) {
                    const infoRow = document.createElement("tr");
                    infoRow.innerHTML = `<td colspan="8" class="bg-warning"><b>TOTAL SCORE ini berasal dari input manual</b>, jika ingin menggunakan nilai hasil koreksi maka nilai input manual harus diisi 0</td></tr>`
                    tbody.appendChild(infoRow);
                }

                table.appendChild(tbody);
                tableContainer.appendChild(table);
                body.appendChild(tableContainer);
                container.appendChild(body);

                // Append container to parent element
                document.getElementById("konten-jawaban").appendChild(container);
            }
        }

        function populateSoalJodohkan(soalJodohkan, skor, info) {
            if (soalJodohkan && soalJodohkan.length > 0 && info.bobot_jodohkan > 0) {
                const container = document.createElement("div");
                container.className = "card card-success card-outline shadow-sm mb-4 collapsed-card";

                // Header
                const header = document.createElement("div");
                header.className = "card-header"; // Match Green Badge

                const title = document.createElement("h6");
                title.className = "card-title";
                title.innerHTML = "<b>III. MENJODOHKAN</b>";

                const tools = document.createElement("div");
                tools.className = "card-tools";

                const collapseButton = document.createElement("button");
                collapseButton.type = "button";
                collapseButton.className = "btn btn-tool";
                // collapseButton.dataset.cardWidget = "collapse";
                collapseButton.title = "Collapse";
                collapseButton.onclick = function () {
                    const card = $(this).closest('.card');
                    const body = card.find('.card-body');
                    const icon = $(this).find('i');
                    if (card.hasClass('collapsed-card')) {
                        card.removeClass('collapsed-card');
                        body.slideDown();
                        icon.removeClass('bi-plus').addClass('bi-dash');
                    } else {
                        card.addClass('collapsed-card');
                        body.slideUp();
                        icon.removeClass('bi-dash').addClass('bi-plus');
                    }
                };

                const collapseIcon = document.createElement("i");
                collapseIcon.className = "bi bi-plus";

                collapseButton.appendChild(collapseIcon);
                tools.appendChild(collapseButton);
                header.appendChild(title);
                header.appendChild(tools);
                container.appendChild(header);

                // Body
                const body = document.createElement("div");
                body.className = "card-body";

                const alert = document.createElement("div");
                alert.className = "alert alert-light-success border border-success border-opacity-25 text-success";
                alert.role = "alert";

                const ul = document.createElement("ul");
                const infoList = [
                    `Bobot soal: <b>${info.bobot_jodohkan}</b>. Jumlah soal: <b>${info.tampil_jodohkan}</b>. Max point persoal: <b>${(info.bobot_jodohkan / info.tampil_jodohkan).toFixed(2)}</b>.`,
                    "Point soal menjodohkan bisa diedit",
                    "Utamakan mengkoreksi hasil analisa yang berwarna kuning",
                    'Klik <i class="bi bi-pencil text-yellow"></i> untuk mengedit point. Klik <i class="bi bi-undo text-blue"></i> untuk mengembalikan ke point otomatis',
                    'Klik tombol <b>SIMPAN</b> untuk menyimpan perubahan point'
                ];

                infoList.forEach(text => {
                    const li = document.createElement("li");
                    li.innerHTML = text;
                    ul.appendChild(li);
                });

                alert.appendChild(ul);
                body.appendChild(alert);

                // Table
                const tableContainer = document.createElement("div");
                tableContainer.className = "table-responsive";

                const table = document.createElement("table");
                table.id = "table-jodohkan";
                table.className = "w-100 table table-striped table-bordered border-success nowrap";

                const thead = document.createElement("thead");
                const headerRow = document.createElement("tr");

                const headers = [
                    { text: "No. Soal", class: "bg-light", width: "50px" },
                    { text: "Soal", class: "bg-light" },
                    { text: "Jawaban Benar", class: "bg-light" },
                    { text: "Jawaban Siswa", class: "bg-light" },
                    { text: "Analisa", class: "bg-light", width: "50px" },
                    { text: `Point<br>Max. ${(info.bobot_jodohkan / info.tampil_jodohkan).toFixed(2)}`, class: "bg-light", width: "80px" },
                    { text: "Aksi", class: "bg-light", width: "80px" }
                ];

                headers.forEach((header, index) => {
                    const th = document.createElement("th");
                    if (index === 5) {
                        th.style.minWidth = '120px'
                    }
                    th.className = `text-center align-middle ${header.class}`;
                    if (header.width) th.style.width = header.width;
                    th.innerHTML = header.text;
                    headerRow.appendChild(th);
                });

                thead.appendChild(headerRow);
                table.appendChild(thead);

                const tbody = document.createElement("tbody");

                soalJodohkan.forEach((soal, index) => {
                    const detail = skor.detail.find(d => d.id_soal === soal.id_soal) || {};
                    const rows = detail.tabel_soal.length;

                    // First Row
                    const firstRow = document.createElement("tr");

                    const cellNo = document.createElement("td");
                    cellNo.className = "text-center";
                    cellNo.rowSpan = rows;
                    cellNo.textContent = index + 1;
                    firstRow.appendChild(cellNo);

                    const cellSoal = document.createElement("td");
                    cellSoal.rowSpan = rows;
                    cellSoal.innerHTML = optimizeImageSrc(soal?.soal);
                    firstRow.appendChild(cellSoal);

                    const cellJawabanBenar = document.createElement("td");
                    cellJawabanBenar.innerHTML = optimizeImageSrc(formatJawaban(detail?.tabel_soal[0]));
                    firstRow.appendChild(cellJawabanBenar);

                    const cellJawabanSiswa = document.createElement("td");
                    cellJawabanSiswa.innerHTML = optimizeImageSrc(formatJawaban(detail?.tabel_jawab[0]));
                    firstRow.appendChild(cellJawabanSiswa);

                    const cellAnalisa = document.createElement("td");
                    cellAnalisa.className = "text-center";
                    cellAnalisa.rowSpan = rows;
                    cellAnalisa.innerHTML = optimizeImageSrc(detail?.analisa) || "--";
                    firstRow.appendChild(cellAnalisa);

                    const cellPoint = document.createElement("td");
                    cellPoint.className = "text-center";
                    cellPoint.rowSpan = rows;

                    const input = document.createElement("input");
                    input.id = `input${soal?.id_soal}`;
                    input.name = `input${soal?.id_soal}`;
                    input.type = "number";
                    input.min = "0";
                    input.max = (info.bobot_jodohkan / info.tampil_jodohkan).toFixed(2);
                    input.step = "0.10";
                    input.style.display = "none";
                    input.value = detail?.skor_jawaban || 0;

                    const span = document.createElement("span");
                    span.className = "jodohkan";
                    span.dataset.idsoal = soal?.id_soal;
                    span.id = `span${soal?.id_soal}`;
                    span.textContent = detail?.skor_jawaban || 0;

                    cellPoint.appendChild(input);
                    cellPoint.appendChild(span);
                    firstRow.appendChild(cellPoint);

                    const cellAksi = document.createElement("td");
                    cellAksi.rowSpan = rows;

                    const editButton = document.createElement("button");
                    editButton.type = "button";
                    editButton.className = "btn btn-sm";
                    editButton.innerHTML = '<i class="bi bi-pencil text-yellow"></i>';
                    editButton.onclick = () => edit(soal?.id_soal, "jodohkan");

                    const undoButton = document.createElement("button");
                    undoButton.type = "button";
                    undoButton.className = "btn btn-sm";
                    undoButton.innerHTML = '<i class="bi bi-undo text-blue"></i>';
                    undoButton.onclick = () => undo(soal?.id_soal, detail?.nilai_asli);

                    cellAksi.appendChild(editButton);
                    cellAksi.appendChild(undoButton);
                    firstRow.appendChild(cellAksi);

                    tbody.appendChild(firstRow);

                    // Additional Rows
                    for (let t = 1; t < rows; t++) {
                        const additionalRow = document.createElement("tr");

                        const additionalCellJawabanBenar = document.createElement("td");
                        additionalCellJawabanBenar.innerHTML = optimizeImageSrc(formatJawaban(detail?.tabel_soal[t]));
                        additionalRow.appendChild(additionalCellJawabanBenar);

                        const additionalCellJawabanSiswa = document.createElement("td");
                        additionalCellJawabanSiswa.innerHTML = optimizeImageSrc(formatJawaban(detail?.tabel_jawab[t]));
                        additionalRow.appendChild(additionalCellJawabanSiswa);

                        tbody.appendChild(additionalRow);
                    }
                });

                const totalRow = document.createElement("tr");
                const totalCell = document.createElement("td");
                totalCell.colSpan = 5;
                totalCell.className = "text-end align-middle text-bold";
                totalCell.textContent = "TOTAL SCORE JODOHKAN";

                const skorAsli = formatNilai(skor.skor_asli)
                const skorTotal = formatNilai(skor.skor_total)

                const totalScoreCell = document.createElement("td");
                totalScoreCell.className = "alert-default-primary text-center text-bold align-middle";
                totalScoreCell.textContent = skorTotal

                const saveCell = document.createElement("td");
                saveCell.className = "align-middle";

                const saveButton = document.createElement("button");
                saveButton.id = "jodohkan";
                saveButton.className = "btn btn-sm btn-primary";
                saveButton.textContent = "Simpan";
                saveButton.onclick = () => simpan(saveButton);
                saveCell.appendChild(saveButton);

                totalRow.appendChild(totalCell);
                totalRow.appendChild(totalScoreCell);
                totalRow.appendChild(saveCell);
                tbody.appendChild(totalRow);

                if (skorAsli !== skorTotal) {
                    const infoRow = document.createElement("tr");
                    infoRow.innerHTML = `<td colspan="8" class="bg-warning"><b>TOTAL SCORE ini berasal dari input manual</b>, jika ingin menggunakan nilai hasil koreksi maka nilai input manual harus diisi 0</td></tr>`
                    tbody.appendChild(infoRow);
                }

                table.appendChild(tbody);
                tableContainer.appendChild(table);
                body.appendChild(tableContainer);
                container.appendChild(body);

                document.getElementById("konten-jawaban").appendChild(container);
            }
        }

        function formatJawaban(jawaban) {
            if (!jawaban) return "--";
            if (jawaban.type_soal === "1" && jawaban.subtitle) {
                return `<span>${jawaban.title}</span><ul>${jawaban.subtitle.map(sub => `<li>${sub}</li>`).join("")}</ul>`;
            }
            return `<p>${jawaban.title || "--"}<br>${jawaban.subtitle ? jawaban.subtitle[0] : ""}</p>`;
        }

        function populateSoalIsian(soal_isian, details_is, info) {
            if (soal_isian && soal_isian.length > 0 && info.bobot_isian > 0) {
                const container = document.createElement('div');
                container.classList.add('card', 'card-default', 'my-shadow', 'mb-4', 'collapsed-card');

                const cardHeader = document.createElement('div');
                cardHeader.classList.add('card-header');
                cardHeader.classList.add('alert-default-warning');
                const headerTitle = document.createElement('h6');
                headerTitle.classList.add('card-title');
                headerTitle.innerHTML = '<b>IV. ISIAN SINGKAT</b>';
                const cardTools = document.createElement('div');
                cardTools.classList.add('card-tools');
                const collapseButton = document.createElement('button');
                collapseButton.type = 'button';
                collapseButton.classList.add('btn', 'btn-tool');
                // collapseButton.setAttribute('data-card-widget', 'collapse');
                collapseButton.setAttribute('title', 'Collapse');
                collapseButton.innerHTML = '<i class="bi bi-plus"></i>';
                collapseButton.onclick = function () {
                    const card = $(this).closest('.card');
                    const body = card.find('.card-body');
                    const icon = $(this).find('i');
                    if (card.hasClass('collapsed-card')) {
                        card.removeClass('collapsed-card');
                        body.slideDown();
                        icon.removeClass('bi-plus').addClass('bi-dash');
                    } else {
                        card.addClass('collapsed-card');
                        body.slideUp();
                        icon.removeClass('bi-dash').addClass('bi-plus');
                    }
                };
                cardTools.appendChild(collapseButton);
                cardHeader.appendChild(headerTitle);
                cardHeader.appendChild(cardTools);
                container.appendChild(cardHeader);

                const cardBody = document.createElement('div');
                cardBody.classList.add('card-body');

                const alert = document.createElement('div');
                alert.classList.add('alert', 'alert-default-success');
                alert.setAttribute('role', 'alert');
                alert.innerHTML = `
        <ul>
            <li>Bobot soal: <b>${info.bobot_isian}</b>. Jumlah soal:
                <b>${info.tampil_isian}</b>. Max point persoal:
                <b>${(info.bobot_isian / info.tampil_isian).toFixed(2)}</b>.
            </li>
            <li>Point soal isian singkat bisa diedit</li>
            <li>Utamakan mengkoreksi hasil analisa yang berwarna kuning</li>
            <li>Klik <i class="bi bi-pencil text-yellow"></i> untuk mengedit point. Klik <i class="bi bi-undo text-blue"></i> untuk mengembalikan ke point otomatis</li>
            <li>Klik tombol <b>SIMPAN</b> untuk menyimpan perubahan point</li>
        </ul>
    `;
                cardBody.appendChild(alert);

                const tableContainer = document.createElement('div');
                tableContainer.classList.add('table-responsive');
                const table = document.createElement('table');
                table.id = 'table-isian';
                table.classList.add('w-100', 'table', 'table-striped', 'table-bordered', 'border-success', 'nowrap');
                table.innerHTML = `
        <thead>
            <tr>
                <th class="text-center align-middle bg-blue" style="width: 50px">No. Soal</th>
                <th class="text-center align-middle bg-blue">Soal</th>
                <th class="text-center align-middle bg-blue" style="width: 50px">Jawaban Benar</th>
                <th class="text-center align-middle bg-teal" style="width: 50px">Jawaban Siswa</th>
                <th class="text-center align-middle bg-teal" style="width: 50px">Analisa</th>
                <th class="text-center align-middle bg-teal" style="min-width: 120px">Point<br>Max. ${formatNilai(info.bobot_isian / info.tampil_isian)}</th>
                <th class="text-center align-middle bg-teal" style="width: 80px">Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;
                const tbody = table.querySelector('tbody');
                let ns = 1;

                soal_isian.forEach((soal, index) => {
                    const detail = details_is.detail.find(detail => detail.id_soal === soal.id_soal);
                    const row = document.createElement('tr');

                    const noSoalCell = document.createElement('td');
                    noSoalCell.classList.add('text-center');
                    noSoalCell.textContent = index + 1;
                    row.appendChild(noSoalCell);

                    const soalCell = document.createElement('td');
                    soalCell.innerHTML = optimizeImageSrc(soal?.soal);
                    row.appendChild(soalCell);

                    const jawabanBenarCell = document.createElement('td');
                    jawabanBenarCell.classList.add('text-center');
                    jawabanBenarCell.textContent = soal?.jawaban;
                    row.appendChild(jawabanBenarCell);

                    const jawabanSiswaCell = document.createElement('td');
                    jawabanSiswaCell.classList.add('text-center');
                    jawabanSiswaCell.textContent = detail?.jawaban_siswa;
                    row.appendChild(jawabanSiswaCell);

                    const analisaCell = document.createElement('td');
                    analisaCell.classList.add('text-center');
                    analisaCell.innerHTML = optimizeImageSrc(detail?.analisa);
                    row.appendChild(analisaCell);

                    const pointCell = document.createElement('td');
                    pointCell.classList.add('text-center');
                    const input = document.createElement('input');
                    input.id = `input${soal?.id_soal}`;
                    input.name = `input${soal?.id_soal}`;
                    input.value = detail?.skor_jawaban || '0';
                    input.type = 'number';
                    input.min = 0;
                    input.max = (info.bobot_isian / info.tampil_isian).toFixed(2);
                    input.step = 0.1;
                    input.style.width = '100%';
                    input.style.boxSizing = 'border-box';
                    input.style.display = 'none';
                    const span = document.createElement('span');
                    span.classList.add('isian');
                    span.setAttribute('data-idsoal', soal?.id_soal);
                    span.id = `span${soal?.id_soal}`;
                    span.textContent = detail?.skor_jawaban || '0';
                    pointCell.appendChild(input);
                    pointCell.appendChild(span);
                    row.appendChild(pointCell);

                    const aksiCell = document.createElement('td');
                    const editButton = document.createElement('button');
                    editButton.id = `edit${soal?.id_soal}`;
                    editButton.type = 'button';
                    editButton.classList.add('btn', 'btn-sm');
                    editButton.onclick = () => edit(soal?.id_soal, "isian");
                    editButton.innerHTML = '<i class="bi bi-pencil text-yellow"></i>';
                    const undoButton = document.createElement('button');
                    undoButton.id = `undo${soal?.id_soal}`;
                    undoButton.type = 'button';
                    undoButton.classList.add('btn', 'btn-sm');
                    undoButton.onclick = () => undo(soal?.id_soal, detail?.nilai_asli);
                    undoButton.innerHTML = '<i class="bi bi-undo text-blue"></i>';
                    aksiCell.appendChild(editButton);
                    aksiCell.appendChild(undoButton);
                    row.appendChild(aksiCell);

                    tbody.appendChild(row);

                    ns++;
                });

                const totalRow = document.createElement('tr');
                const totalLabelCell = document.createElement('td');
                totalLabelCell.colSpan = 5;
                totalLabelCell.classList.add('text-end', 'text-bold', 'align-middle');
                totalLabelCell.textContent = 'TOTAL SCORE ISIAN SINGKAT';
                totalRow.appendChild(totalLabelCell);

                const skorAsli = formatNilai(details_is.skor_asli)
                const skorTotal = formatNilai(details_is.skor_total)

                const totalScoreCell = document.createElement('td');
                totalScoreCell.classList.add('alert-default-primary', 'text-center', 'text-bold');
                totalScoreCell.textContent = skorTotal;
                totalRow.appendChild(totalScoreCell);

                const saveCell = document.createElement('td');
                const saveButton = document.createElement('button');
                saveButton.id = 'isian';
                saveButton.setAttribute('data-max', (info.bobot_isian / info.tampil_isian).toFixed(2));
                saveButton.classList.add('btn', 'btn-sm', 'btn-primary');
                saveButton.onclick = () => simpan(saveButton);
                saveButton.textContent = 'Simpan';
                saveCell.appendChild(saveButton);
                totalRow.appendChild(saveCell);
                tbody.appendChild(totalRow);

                if (skorAsli !== skorTotal) {
                    const infoRow = document.createElement("tr");
                    infoRow.innerHTML = `<td colspan="8" class="bg-warning"><b>TOTAL SCORE ini berasal dari input manual</b>, jika ingin menggunakan nilai hasil koreksi maka nilai input manual harus diisi 0</td></tr>`
                    tbody.appendChild(infoRow);
                }

                tableContainer.appendChild(table)
                cardBody.appendChild(tableContainer)
                container.appendChild(cardBody);

                document.getElementById("konten-jawaban").appendChild(container);
            }
        }

        function populateSoalEsai(soal_esai, details_es, info) {
            if (soal_esai && soal_esai.length > 0 && info.bobot_esai > 0) {
                const container = document.createElement('div');
                container.classList.add('card', 'card-default', 'my-shadow', 'mb-4', 'collapsed-card');

                const cardHeader = document.createElement('div');
                cardHeader.classList.add('card-header');
                cardHeader.classList.add('alert-default-info'); // Use Info/Purple for Esai
                const headerTitle = document.createElement('h6');
                headerTitle.classList.add('card-title');
                headerTitle.innerHTML = '<b>V. URAIAN</b>';
                const cardTools = document.createElement('div');
                cardTools.classList.add('card-tools');
                const collapseButton = document.createElement('button');
                collapseButton.type = 'button';
                collapseButton.classList.add('btn', 'btn-tool');
                // collapseButton.setAttribute('data-card-widget', 'collapse');
                collapseButton.setAttribute('title', 'Collapse');
                collapseButton.innerHTML = '<i class="bi bi-plus"></i>';
                collapseButton.onclick = function () {
                    const card = $(this).closest('.card');
                    const body = card.find('.card-body');
                    const icon = $(this).find('i');
                    if (card.hasClass('collapsed-card')) {
                        card.removeClass('collapsed-card');
                        body.slideDown();
                        icon.removeClass('bi-plus').addClass('bi-dash');
                    } else {
                        card.addClass('collapsed-card');
                        body.slideUp();
                        icon.removeClass('bi-dash').addClass('bi-plus');
                    }
                };
                cardTools.appendChild(collapseButton);
                cardHeader.appendChild(headerTitle);
                cardHeader.appendChild(cardTools);
                container.appendChild(cardHeader);

                const cardBody = document.createElement('div');
                cardBody.classList.add('card-body');

                const alert = document.createElement('div');
                alert.classList.add('alert', 'alert-default-success');
                alert.setAttribute('role', 'alert');
                alert.innerHTML = `
        <ul>
            <li>Bobot soal: <b>${info.bobot_esai}</b>. Jumlah soal:
                <b>${info.tampil_esai}</b>. Max point persoal:
                <b>${(info.bobot_esai / info.tampil_esai).toFixed(2)}</b>.
            </li>
            <li>Point soal uraian bisa diedit</li>
            <li>Utamakan mengkoreksi hasil analisa yang berwarna kuning</li>
            <li>Klik <i class="bi bi-pencil text-yellow"></i> untuk mengedit point. Klik <i class="bi bi-undo text-blue"></i> untuk mengembalikan ke point otomatis</li>
            <li>Klik tombol <b>SIMPAN</b> untuk menyimpan perubahan point</li>
        </ul>
    `;
                cardBody.appendChild(alert);

                const tableContainer = document.createElement('div');
                tableContainer.classList.add('table-responsive');
                const table = document.createElement('table');
                table.id = 'table-essai';
                table.classList.add('w-100', 'table', 'table-striped', 'table-bordered', 'border-success', 'nowrap');
                table.innerHTML = `
        <thead>
            <tr>
                <th class="text-center align-middle bg-blue" style="width: 50px">No. Soal</th>
                <th class="text-center align-middle bg-blue">Soal</th>
                <th class="text-center align-middle bg-blue" style="width: 50px">Jawaban Benar</th>
                <th class="text-center align-middle bg-teal" style="width: 50px">Jawaban Siswa</th>
                <th class="text-center align-middle bg-teal" style="width: 50px">Analisa</th>
                <th class="text-center align-middle bg-teal" style="min-width: 120px">Point<br>Max. ${(info.bobot_esai / info.tampil_esai).toFixed(2)}</th>
                <th class="text-center align-middle bg-teal" style="width: 80px">Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    `;
                const tbody = table.querySelector('tbody');
                let ns = 1;

                soal_esai.forEach((soal, index) => {
                    const detail = details_es.detail.find(detail => detail.id_soal === soal.id_soal);

                    const row = document.createElement('tr');

                    const noSoalCell = document.createElement('td');
                    noSoalCell.classList.add('text-center');
                    noSoalCell.textContent = index + 1;
                    row.appendChild(noSoalCell);

                    const soalCell = document.createElement('td');
                    soalCell.innerHTML = optimizeImageSrc(soal?.soal);
                    row.appendChild(soalCell);

                    const jawabanBenarCell = document.createElement('td');
                    jawabanBenarCell.classList.add('text-center');
                    jawabanBenarCell.innerHTML = optimizeImageSrc(soal?.jawaban) || '--';
                    row.appendChild(jawabanBenarCell);

                    const jawabanSiswaCell = document.createElement('td');
                    jawabanSiswaCell.classList.add('text-center');
                    jawabanSiswaCell.innerHTML = optimizeImageSrc(detail?.jawaban_siswa) || '-';
                    row.appendChild(jawabanSiswaCell);

                    const analisaCell = document.createElement('td');
                    analisaCell.classList.add('text-center');
                    analisaCell.innerHTML = optimizeImageSrc(detail?.analisa) || '<i class="bi bi-x-circle text-yellow text-lg"></i>';
                    row.appendChild(analisaCell);

                    const pointCell = document.createElement('td');
                    pointCell.classList.add('text-center');
                    const input = document.createElement('input');
                    input.id = `input${soal?.id_soal}`;
                    input.name = `input${soal?.id_soal}`;
                    input.value = detail?.skor_jawaban || '0';
                    input.type = 'number';
                    input.min = 0;
                    input.max = (info.bobot_esai / info.tampil_esai).toFixed(2);
                    input.step = 0.10;
                    input.style.width = '100%';
                    input.style.boxSizing = 'border-box';
                    input.style.display = 'none';

                    const span = document.createElement('span');
                    span.classList.add('essai');
                    span.setAttribute('data-idsoal', soal?.id_soal);
                    span.id = `span${soal?.id_soal}`;
                    span.textContent = detail?.skor_jawaban || '0';

                    pointCell.appendChild(input);
                    pointCell.appendChild(span);
                    row.appendChild(pointCell);

                    const aksiCell = document.createElement('td');
                    const editButton = document.createElement('button');
                    editButton.id = `edit${soal?.id_soal}`;
                    editButton.type = 'button';
                    editButton.classList.add('btn', 'btn-sm');
                    editButton.onclick = () => edit(soal?.id_soal, "essai");
                    editButton.innerHTML = '<i class="bi bi-pencil text-yellow"></i>';

                    const undoButton = document.createElement('button');
                    undoButton.id = `undo${soal?.id_soal}`;
                    undoButton.type = 'button';
                    undoButton.classList.add('btn', 'btn-sm');
                    undoButton.onclick = () => undo(soal?.id_soal, detail?.nilai_asli);
                    undoButton.innerHTML = '<i class="bi bi-undo text-blue"></i>';
                    aksiCell.appendChild(editButton);
                    aksiCell.appendChild(undoButton);
                    row.appendChild(aksiCell);

                    tbody.appendChild(row);

                    ns++;
                });

                const totalRow = document.createElement('tr');
                const totalLabelCell = document.createElement('td');
                totalLabelCell.colSpan = 5;
                totalLabelCell.classList.add('text-end', 'text-bold', 'align-middle');
                totalLabelCell.textContent = 'TOTAL SCORE URAIAN';
                totalRow.appendChild(totalLabelCell);

                const skorAsli = formatNilai(details_es.skor_asli)
                const skorTotal = formatNilai(details_es.skor_total)

                const totalScoreCell = document.createElement('td');
                totalScoreCell.classList.add('alert-default-primary', 'text-center', 'text-bold');
                totalScoreCell.textContent = skorTotal;
                totalRow.appendChild(totalScoreCell);

                const saveCell = document.createElement('td');
                const saveButton = document.createElement('button');
                saveButton.id = 'essai';
                saveButton.setAttribute('data-max', (info.bobot_esai / info.tampil_esai).toFixed(2));
                saveButton.classList.add('btn', 'btn-sm', 'btn-primary');
                saveButton.onclick = () => simpan(saveButton);
                saveButton.textContent = 'Simpan';
                saveCell.appendChild(saveButton);
                totalRow.appendChild(saveCell);
                tbody.appendChild(totalRow);

                if (skorAsli !== skorTotal) {
                    const infoRow = document.createElement("tr");
                    infoRow.innerHTML = `<td colspan="8" class="bg-warning"><b>TOTAL SCORE ini berasal dari input manual</b>, jika ingin menggunakan nilai hasil koreksi maka nilai input manual harus diisi 0</td></tr>`
                    tbody.appendChild(infoRow);
                }

                tableContainer.appendChild(table)
                cardBody.appendChild(tableContainer)
                container.appendChild(cardBody);

                document.getElementById("konten-jawaban").appendChild(container);
            }
        }

        function populateSoalUrutan(soal_urutan, details_urutan, info) {
            if (soal_urutan && soal_urutan.length > 0 && info.bobot_urutan > 0) {
                const container = document.createElement('div');
                container.classList.add('card', 'card-secondary', 'card-outline', 'shadow-sm', 'mb-4', 'collapsed-card');

                const cardHeader = document.createElement('div');
                cardHeader.classList.add('card-header'); // Match Pink/Secondary Badge
                cardHeader.innerHTML = `
                    <h6 class="card-title"><b>VI. URUTAN</b></h6>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="Collapse">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>`;
                const btnCollapse = cardHeader.querySelector('.btn-tool');
                btnCollapse.onclick = function () {
                    const card = $(this).closest('.card');
                    const body = card.find('.card-body');
                    const icon = $(this).find('i');
                    if (card.hasClass('collapsed-card')) {
                        card.removeClass('collapsed-card');
                        body.slideDown();
                        icon.removeClass('bi-plus').addClass('bi-dash');
                    } else {
                        card.addClass('collapsed-card');
                        body.slideUp();
                        icon.removeClass('bi-dash').addClass('bi-plus');
                    }
                };
                container.appendChild(cardHeader);

                const cardBody = document.createElement('div');
                cardBody.classList.add('card-body');

                const alert = document.createElement('div');
                alert.classList.add('alert', 'alert-light-secondary', 'border', 'border-secondary', 'border-opacity-25', 'text-secondary');
                alert.innerHTML = `
                    <ul>
                        <li>Bobot soal: <b>${info.bobot_urutan}</b>. Jumlah soal: <b>${info.tampil_urutan}</b>.
                        Max point persoal: <b>${(info.bobot_urutan / info.tampil_urutan).toFixed(2)}</b>.</li>
                        <li>Klik tombol <b>SIMPAN</b> untuk menyimpan perubahan point</li>
                    </ul>`;
                cardBody.appendChild(alert);

                const tableContainer = document.createElement('div');
                tableContainer.classList.add('table-responsive');
                const table = document.createElement('table');
                table.id = 'table-urutan';
                table.classList.add('w-100', 'table', 'table-striped', 'table-bordered', 'border-success', 'nowrap');

                table.innerHTML = `
                    <thead>
                        <tr>
                            <th class="text-center align-middle bg-light" style="width: 50px">No.</th>
                            <th class="text-center align-middle bg-light">Soal</th>
                            <th class="text-center align-middle bg-light">Jawaban Benar</th>
                            <th class="text-center align-middle bg-light">Jawaban Siswa</th>
                            <th class="text-center align-middle bg-light" style="width: 50px">Analisa</th>
                            <th class="text-center align-middle bg-light" style="min-width: 120px">Point</th>
                        </tr>
                    </thead>
                    <tbody></tbody>`;

                const tbody = table.querySelector('tbody');

                soal_urutan.forEach((soal, index) => {
                    const detail = details_urutan.detail.find(d => d.id_soal === soal.id_soal) || {};
                    const row = document.createElement('tr');

                    let jawabBenarHtml = '';
                    try {
                        let parsedBenar = detail.jawaban_benar;
                        if (typeof parsedBenar === 'string') parsedBenar = JSON.parse(parsedBenar);
                        if (Array.isArray(parsedBenar)) {
                            jawabBenarHtml = '<ol>' + parsedBenar.map(it => `<li>${it}</li>`).join('') + '</ol>';
                        }
                    } catch (e) { jawabBenarHtml = String(detail.jawaban_benar); }

                    let jawabSiswaHtml = '';
                    try {
                        let parsedSiswa = detail.jawaban_siswa;
                        if (typeof parsedSiswa === 'string') parsedSiswa = JSON.parse(parsedSiswa);
                        // Sometimes it maps to objects
                        if (parsedSiswa && typeof parsedSiswa === 'object') {
                            // If it's array
                            if (Array.isArray(parsedSiswa)) {
                                jawabSiswaHtml = '<ol>' + parsedSiswa.map(it => `<li>${it}</li>`).join('') + '</ol>';
                            } else {
                                jawabSiswaHtml = JSON.stringify(parsedSiswa);
                            }
                        } else {
                            jawabSiswaHtml = String(parsedSiswa || '-');
                        }
                    } catch (e) { jawabSiswaHtml = String(detail.jawaban_siswa || '-'); }

                    row.innerHTML = `
                        <td class="text-center">${index + 1}</td>
                        <td>${optimizeImageSrc(soal.soal)}</td>
                        <td>${jawabBenarHtml}</td>
                        <td>${jawabSiswaHtml}</td>
                        <td class="text-center">${optimizeImageSrc(detail.analisa)}</td>
                        <td class="text-center">
                            <span class="urutan" id="span${soal.id_soal}" data-idsoal="${soal.id_soal}">${detail.skor_jawaban || 0}</span>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const totalRow = document.createElement('tr');
                const skorTotal = formatNilai(details_urutan.skor_total);
                totalRow.innerHTML = `
                    <td colspan="5" class="text-end text-bold align-middle">TOTAL SCORE URUTAN</td>
                    <td class="bg-light text-center text-bold text-secondary">${skorTotal}</td>
                `;
                tbody.appendChild(totalRow);

                tableContainer.appendChild(table);
                cardBody.appendChild(tableContainer);
                container.appendChild(cardBody);
                document.getElementById("konten-jawaban").appendChild(container);
            }
        }

        function populateSoalCloze(soal_cloze, details_cloze, info) {
            if (soal_cloze && soal_cloze.length > 0 && info.bobot_cloze > 0) {
                const container = document.createElement('div');
                container.classList.add('card', 'card-dark', 'card-outline', 'shadow-sm', 'mb-4', 'collapsed-card');

                const cardHeader = document.createElement('div');
                cardHeader.classList.add('card-header'); // Match Indigo/Dark Badge
                cardHeader.innerHTML = `
                    <h6 class="card-title"><b>VII. ISIAN RUMPANG</b></h6>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" title="Collapse">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>`;
                const btnCollapse = cardHeader.querySelector('.btn-tool');
                btnCollapse.onclick = function () {
                    const card = $(this).closest('.card');
                    const body = card.find('.card-body');
                    const icon = $(this).find('i');
                    if (card.hasClass('collapsed-card')) {
                        card.removeClass('collapsed-card');
                        body.slideDown();
                        icon.removeClass('bi-plus').addClass('bi-dash');
                    } else {
                        card.addClass('collapsed-card');
                        body.slideUp();
                        icon.removeClass('bi-dash').addClass('bi-plus');
                    }
                };
                container.appendChild(cardHeader);

                const cardBody = document.createElement('div');
                cardBody.classList.add('card-body');

                const alert = document.createElement('div');
                alert.classList.add('alert', 'alert-light-dark', 'border', 'border-dark', 'border-opacity-25', 'text-dark');
                alert.innerHTML = `
                    <ul>
                        <li>Bobot soal: <b>${info.bobot_cloze}</b>. Jumlah soal: <b>${info.tampil_cloze}</b>.
                        Max point persoal: <b>${(info.bobot_cloze / info.tampil_cloze).toFixed(2)}</b>.</li>
                        <li>Klik tombol <b>SIMPAN</b> untuk menyimpan perubahan point</li>
                    </ul>`;
                cardBody.appendChild(alert);

                const tableContainer = document.createElement('div');
                tableContainer.classList.add('table-responsive');
                const table = document.createElement('table');
                table.id = 'table-cloze';
                table.classList.add('w-100', 'table', 'table-striped', 'table-bordered', 'border-dark', 'nowrap');

                table.innerHTML = `
                    <thead>
                        <tr>
                            <th class="text-center align-middle bg-light" style="width: 50px">No.</th>
                            <th class="text-center align-middle bg-light">Soal</th>
                            <th class="text-center align-middle bg-light">Rincian Jawaban</th>
                            <th class="text-center align-middle bg-light" style="width: 50px">Analisa</th>
                            <th class="text-center align-middle bg-light" style="min-width: 80px">Point</th>
                        </tr>
                    </thead>
                    <tbody></tbody>`;

                const tbody = table.querySelector('tbody');

                soal_cloze.forEach((soal, index) => {
                    const detail = details_cloze.detail.find(d => d.id_soal === soal.id_soal) || {};
                    const row = document.createElement('tr');

                    let blankDetails = '<ul class="pl-3">';
                    if (detail.blank_results) {
                        const obj = typeof detail.blank_results === 'string' ? JSON.parse(detail.blank_results) : detail.blank_results;
                        // obj is object with keys
                        Object.keys(obj).forEach(k => {
                            const item = obj[k]; // {correct, student, is_correct}
                            const icon = item.is_correct ? '<i class="bi bi-check text-green"></i>' : '<i class="bi bi-x text-red"></i>';
                            blankDetails += `<li>${k}: Benar=<b>${item.correct}</b>, Siswa=<b>${item.student}</b> ${icon}</li>`;
                        });
                    }
                    blankDetails += '</ul>';

                    row.innerHTML = `
                        <td class="text-center">${index + 1}</td>
                        <td>${optimizeImageSrc(soal.soal)}</td>
                        <td>${blankDetails}</td>
                        <td class="text-center">${optimizeImageSrc(detail.analisa)}</td>
                        <td class="text-center">
                            <span class="cloze" id="span${soal.id_soal}" data-idsoal="${soal.id_soal}">${detail.skor_jawaban || 0}</span>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const totalRow = document.createElement('tr');
                const skorTotal = formatNilai(details_cloze.skor_total);
                totalRow.innerHTML = `
                    <td colspan="4" class="text-end text-bold align-middle">TOTAL SCORE ISIAN RUMPANG</td>
                    <td class="bg-light text-center text-bold text-dark">${skorTotal}</td>
                `;
                tbody.appendChild(totalRow);

                tableContainer.appendChild(table);
                cardBody.appendChild(tableContainer);
                container.appendChild(cardBody);
                document.getElementById("konten-jawaban").appendChild(container);
            }
        }

        function formatNilai(nilai) {
            let result = nilai.toFixed(2)
            if (result.includes(".00")) result = result.replace(".00", "")
            return result
        }

        function edit(id, idBtn) {
            var input = $(`#input${id}`);
            var span = $(`#span${id}`);
            var btnedit = $(`#edit${id}`);
            var btnSave = $(`#${idBtn}`);

            if (input.is(":visible")) {
                input.hide();
                span.text(input.val()).show();
                btnedit.html(`<i class="bi bi-pencil text-yellow"></i>`);
                btnSave.removeAttr('disabled')
            } else {
                span.hide();
                input.val(span.text()).show();
                btnedit.html(`<i class="bi bi-check"></i>`);
                btnSave.attr('disabled', 'disabled')
            }
        }

        function undo(id, nilai) {
            var input = $(`#input${id}`);
            var span = $(`#span${id}`);
            input.val(nilai);
            span.text(nilai);
        }

        function simpan(btn) {
            var id = $(btn).attr('id');
            var key;
            if (id === 'pg') {
                key = 'pg_nilai';
            } else if (id === 'pg2') {
                key = 'kompleks_nilai';
            } else if (id === 'jodohkan') {
                key = 'jodohkan_nilai';
            } else if (id === 'isian') {
                key = 'isian_nilai';
            } else if (id === 'essai') {
                key = 'essai_nilai';
            } else if (id === 'urutan') {
                key = 'urutan_nilai';
            } else if (id === 'cloze') {
                key = 'cloze_nilai';
            }

            var loading = $(`#loading-${id}`);
            var max = $(btn).data('max');
            var $nilai = $(`#table-${id}`).find(`.${id}`);
            var json = [];
            $.each($nilai, function () {
                var n = $(this).text();
                if (n > max) {
                    showDangerToast("Point persoal harus kurang dari " + max);
                    json = [];
                    return false;
                }
                if ($(this).is(":hidden")) {
                    showDangerToast("Klik tombol &#10004; dulu");
                    json = [];
                    return false;
                }

                var item = {};
                item['id_soal'] = $(this).data('idsoal');
                //item['jenis'] = jenis;
                item['koreksi'] = $(this).text();
                json.push(item);
            });

            let formData = new FormData($('#koreksi')[0]);
            formData.append('field', key)
            formData.append('nilai', JSON.stringify(json))

            if (json.length > 0) {
                loading.removeClass('d-none');
                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
                            }
                });
                $.ajax({
                    url: base_url + "cbtnilai/simpankoreksi",
                    type: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (data) {
                        console.log(data);
                        if (data.success > 0) {
                            Swal.fire({
                                title: "Berhasil",
                                text: "Koreksi nilai berhasil disimpan",
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            loading.addClass('d-none');
                            Swal.fire({
                                title: "Gagal",
                                text: 'Tidak ada nilai yang disimpan',
                                icon: "error"
                            });
                        }
                    }, error: function (xhr, status, error) {
                        loading.addClass('d-none');
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        Swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            }
        }

        function optimizeImageSrc(content) {
            if (!content) return content;
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            var images = tempDiv.getElementsByTagName('img');
            var changed = false;
            for (var i = 0; i < images.length; i++) {
                var img = images[i];
                var src = img.getAttribute('src');
                if (src && src.includes('uploads')) {
                    var split = src.split('uploads');
                    var newPath = split[1];
                    // Only replace if it doesn't already start with base_url to avoid double prefixing if run multiple times
                    if (src.indexOf(base_url) !== 0) {
                        img.setAttribute('src', base_url + 'uploads' + newPath);
                        changed = true;
                    }
                }
            }
            return changed ? tempDiv.innerHTML : content;
        }
    </script>