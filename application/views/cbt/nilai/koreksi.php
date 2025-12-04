<?php


?>

<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Nama<br /><b><?= $siswa->siswa->nama ?? '' ?></b></td>
                                </tr>
                                <tr>
                                    <td>N I S<br /><b><?= $siswa->siswa->nis ?? '' ?></b></td>
                                </tr>
                                <tr>
                                    <td>Kelas<br /><b><?= $siswa->kelas->nama_kelas ?? $siswa->kelas ?></b></td>
                                </tr>
                                <tr>
                                    <td>No. Peserta<br /><b><?= $siswa->peserta->nomor_peserta ?? $siswa->no_peserta ?></b></td>
                                </tr>
                                <tr>
                                    <td>Sesi<br /><b><?= $siswa->sesi->kode_sesi ?? $siswa->ruang_sesi->sesi->kode_sesi?></b></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-4">
                            <table class="table table-sm table-borderless" id="table-status">
                                <tr>
                                <tr>
                                    <td>Ruang<br /><b><?= $siswa->ruang->kode_ruang ?? $siswa->ruang_sesi->ruang->kode_ruang ?></b></td>
                                </tr>
                                <td>Mata Pelajaran<br /><b><?= $info->mapel->nama_mapel ?? $rekap_mapel ?></b></td>
                                </tr>
                                <tr>
                                    <td>Guru<br /><b><?= $info->guru->nama_guru ?? $rekap_guru ?></b></td>
                                </tr>
                                <tr>
                                    <td>Jenis Ujian<br /><b><?= $jenis->kode_jenis ?></b></td>
                                </tr>
                                <tr>
                                    <td>Tahun Pelajaran<br /><b><?= $tp_jadwal ?? "--/--" ?></b></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-4">
                            <?php
                            $hanyaPG = count($soal_pg) > 0 && count($soal_kompleks) === 0 &&
                                count($soal_jodohkan) === 0 && count($soal_isian) === 0 && count($soal_esai) === 0;
                            ?>
                            <table class="table table-sm table-bordered" id="table-status">
                                <tr>
                                    <td>PG: <?= round($siswa->nilai_akhir->skor_pg['skor_total'] ?? 0, 2) ?></td>
                                    <td rowspan="3" class="text-center align-middle alert-default-warning">
                                        <b>NILAI</b>
                                        <br>
                                        <span style="font-size: 25pt;"><?= round($siswa->nilai_akhir->skor_total ?? 0, 2) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>PK: <?= round($siswa->nilai_akhir->skor_kompleks['skor_total'] ?? 0, 2) ?></td>
                                </tr>
                                <tr>
                                    <td>JO: <?= round($siswa->nilai_akhir->skor_jodohkan['skor_total'] ?? 0, 2) ?></td>
                                </tr>
                                <tr>
                                    <td>IS: <?= round($siswa->nilai_akhir->skor_isian['skor_total'] ?? 0, 2) ?></td>
                                    <td rowspan="2" style="width: 100px" class="text-center alert-default-primary">
                                        <b><?= $hanyaPG ? 'Soal PG tidak perlu koreksi'
                                                : (isset($siswa->nilai_akhir->dikoreksi) && $siswa->nilai_akhir->dikoreksi ? "Sudah dikoreksi" : "Belum dikoreksi")?></b>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ES: <?= round($siswa->nilai_akhir->skor_essai['skor_total'] ?? 0, 2) ?></td>
                                </tr>
                            </table>
                            <?php if (!$hanyaPG) :?>
                            <button class="float-right btn btn-success" id="btn-marked">Tandai Sudah Dikoreksi</button>
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
<input type="hidden" name="is_rekap" value="<?=$is_rekap??'0'?>">
<input type="hidden" name="siswa" value="<?=$siswa->siswa_id ?? $siswa->id_siswa?>">
<input type="hidden" name="jadwal" value="<?=$id_jadwal?>">
<input type="hidden" name="bank" value="<?=$id_bank?>">
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

        setTimeout(function () {
            var pathUpload = 'uploads';
            $.each($('img'), function () {
                var curSrc = $(this).attr('src');
                if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
                } else {
                    var forReplace = curSrc.split(pathUpload);
                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                }
            });

            $.each($(`video`), function () {
                var curSrc = $(this).attr('src');
                if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
                } else {
                    var forReplace = curSrc.split(pathUpload);
                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                }
            });

            $.each($(`audio`), function () {
                var curSrc = $(this).attr('src');
                if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
                } else {
                    var forReplace = curSrc.split(pathUpload);
                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                }
            });
        }, 1000)
        $('#btn-marked').on('click', function () {
            $(this).attr('disabled', 'disabled');
            var dataPost = $('#koreksi').serialize();
            console.log(dataPost)
            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
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
                        swal.fire({
                            title: "Berhasil",
                            text: "Jawaban berhasil ditandai",
                            icon: "success"
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    } else {
                        swal.fire({
                            title: "Gagal",
                            text: 'Tidak bisa menandai',
                            icon: "error"
                        });
                    }
                }, error: function (xhr, status, error) {
                    $('#btn-marked').removeAttr('disabled');
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
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
            container.className = "card card-default my-shadow mb-4 collapsed-card";

            // Card Header
            const header = document.createElement("div");
            header.className = "card-header alert-default-primary";

            const title = document.createElement("h6");
            title.className = "card-title";
            title.innerHTML = "<b>I. PILIHAN GANDA</b>";

            const tools = document.createElement("div");
            tools.className = "card-tools";

            const button = document.createElement("button");
            button.type = "button";
            button.className = "btn btn-tool";
            button.dataset.cardWidget = "collapse";
            button.title = "Collapse";

            const icon = document.createElement("i");
            icon.className = "fas fa-plus";

            button.appendChild(icon);
            tools.appendChild(button);
            header.appendChild(title);
            header.appendChild(tools);
            container.appendChild(header);

            // Card Body
            const body = document.createElement("div");
            body.className = "card-body";

            const alert = document.createElement("div");
            alert.className = "alert alert-default-success";
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
            table.className = "w-100 table table-striped table-bordered border-success nowrap";

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
                th.className = "text-center align-middle bg-blue";
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
                cellNo.textContent = soal?.nomor_soal;
                row.appendChild(cellNo);

                // Soal
                const cellSoal = document.createElement("td");
                cellSoal.innerHTML = soal?.soal;
                row.appendChild(cellSoal);

                // Pilihan
                const cellPilihan = document.createElement("td");
                const ol = document.createElement("ol");
                ol.type = "A";

                ["opsi_a", "opsi_b", "opsi_c", "opsi_d", "opsi_e"].forEach((key, i) => {
                    if (i >= info.opsi) return;
                    const li = document.createElement("li");
                    li.innerHTML = soal[key];
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
                cellAnalisa.innerHTML = detail?.analisa || "";
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
            totalCell.className = "text-right text-bold";
            totalCell.textContent = "TOTAL SCORE PILIHAN GANDA";

            const totalScoreCell = document.createElement("td");
            totalScoreCell.className = "alert-default-primary text-center text-bold";
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
            container.className = "card card-default my-shadow mb-4 collapsed-card";

            // Card Header
            const header = document.createElement("div");
            header.className = "card-header alert-default-info";

            const title = document.createElement("h6");
            title.className = "card-title";
            title.innerHTML = "<b>II. PILIHAN GANDA KOMPLEKS</b>";

            const tools = document.createElement("div");
            tools.className = "card-tools";

            const button = document.createElement("button");
            button.type = "button";
            button.className = "btn btn-tool";
            button.dataset.cardWidget = "collapse";
            button.title = "Collapse";

            const icon = document.createElement("i");
            icon.className = "fas fa-plus";

            button.appendChild(icon);
            tools.appendChild(button);
            header.appendChild(title);
            header.appendChild(tools);
            container.appendChild(header);

            // Card Body
            const body = document.createElement("div");
            body.className = "card-body";

            const alert = document.createElement("div");
            alert.className = "alert alert-default-success";
            alert.role = "alert";

            const ul = document.createElement("ul");
            const infoList = [
                `Bobot soal: <b>${info.bobot_kompleks}</b>. Jumlah soal: <b>${info.tampil_kompleks}</b>. Max point persoal: <b>${(info.bobot_kompleks / info.tampil_kompleks).toFixed(2)}</b>.`,
                "Point soal PG Kompleks bisa diedit",
                "Utamakan mengkoreksi hasil analisa yang berwarna kuning",
                'Klik <i class="fa fa-pencil text-yellow"></i> untuk mengedit point. Klik <i class="fa fa-undo text-blue"></i> untuk mengembalikan ke point otomatis',
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
            table.className = "w-100 table table-striped table-bordered border-success nowrap";

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
                th.className = "text-center align-middle bg-blue";
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
                cellSoal.innerHTML = soal?.soal;
                row.appendChild(cellSoal);

                // Pilihan
                const cellPilihan = document.createElement("td");
                const ol = document.createElement("ol");
                ol.type = "A";

                for (const option in soal?.opsi_a) {
                    const li = document.createElement("li");
                    li.innerHTML = soal?.opsi_a[option];
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
                cellAnalisa.innerHTML = detail?.analisa || "";
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
                editButton.innerHTML = '<i class="fa fa-pencil text-yellow"></i>';
                editButton.onclick = () => edit(soal?.id_soal, "pg2");

                const undoButton = document.createElement("button");
                undoButton.id = `undo${soal?.id_soal}`;
                undoButton.className = "btn btn-sm";
                undoButton.innerHTML = '<i class="fa fa-undo text-blue"></i>';
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
            totalCell.className = "text-right align-middle text-bold";
            totalCell.textContent = "TOTAL SCORE PILIHAN GANDA KOMPLEKS";

            const totalScoreCell = document.createElement("td");
            totalScoreCell.className = "alert-default-primary text-center text-bold align-middle";
            const skorAsli = formatNilai(detailsPg2.skor_asli)
            const skorTotal = formatNilai(detailsPg2.skor_total)
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
            container.className = "card card-default my-shadow mb-4 collapsed-card";

            // Header
            const header = document.createElement("div");
            header.className = "card-header alert-default-danger";

            const title = document.createElement("h6");
            title.className = "card-title";
            title.innerHTML = "<b>III. MENJODOHKAN</b>";

            const tools = document.createElement("div");
            tools.className = "card-tools";

            const collapseButton = document.createElement("button");
            collapseButton.type = "button";
            collapseButton.className = "btn btn-tool";
            collapseButton.dataset.cardWidget = "collapse";
            collapseButton.title = "Collapse";

            const collapseIcon = document.createElement("i");
            collapseIcon.className = "fas fa-plus";

            collapseButton.appendChild(collapseIcon);
            tools.appendChild(collapseButton);
            header.appendChild(title);
            header.appendChild(tools);
            container.appendChild(header);

            // Body
            const body = document.createElement("div");
            body.className = "card-body";

            const alert = document.createElement("div");
            alert.className = "alert alert-default-success";
            alert.role = "alert";

            const ul = document.createElement("ul");
            const infoList = [
                `Bobot soal: <b>${info.bobot_jodohkan}</b>. Jumlah soal: <b>${info.tampil_jodohkan}</b>. Max point persoal: <b>${(info.bobot_jodohkan / info.tampil_jodohkan).toFixed(2)}</b>.`,
                "Point soal menjodohkan bisa diedit",
                "Utamakan mengkoreksi hasil analisa yang berwarna kuning",
                'Klik <i class="fa fa-pencil text-yellow"></i> untuk mengedit point. Klik <i class="fa fa-undo text-blue"></i> untuk mengembalikan ke point otomatis',
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
                { text: "No. Soal", class: "bg-blue", width: "50px" },
                { text: "Soal", class: "bg-blue" },
                { text: "Jawaban Benar", class: "bg-blue" },
                { text: "Jawaban Siswa", class: "bg-teal" },
                { text: "Analisa", class: "bg-teal", width: "50px" },
                { text: `Point<br>Max. ${(info.bobot_jodohkan / info.tampil_jodohkan).toFixed(2)}`, class: "bg-teal", width: "80px" },
                { text: "Aksi", class: "bg-teal", width: "80px" }
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
                cellSoal.innerHTML = soal?.soal;
                firstRow.appendChild(cellSoal);

                const cellJawabanBenar = document.createElement("td");
                cellJawabanBenar.innerHTML = formatJawaban(detail?.tabel_soal[0]);
                firstRow.appendChild(cellJawabanBenar);

                const cellJawabanSiswa = document.createElement("td");
                cellJawabanSiswa.innerHTML = formatJawaban(detail?.tabel_jawab[0]);
                firstRow.appendChild(cellJawabanSiswa);

                const cellAnalisa = document.createElement("td");
                cellAnalisa.className = "text-center";
                cellAnalisa.rowSpan = rows;
                cellAnalisa.innerHTML = detail?.analisa || "--";
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
                editButton.innerHTML = '<i class="fa fa-pencil text-yellow"></i>';
                editButton.onclick = () => edit(soal?.id_soal, "jodohkan");

                const undoButton = document.createElement("button");
                undoButton.type = "button";
                undoButton.className = "btn btn-sm";
                undoButton.innerHTML = '<i class="fa fa-undo text-blue"></i>';
                undoButton.onclick = () => undo(soal?.id_soal, detail?.nilai_asli);

                cellAksi.appendChild(editButton);
                cellAksi.appendChild(undoButton);
                firstRow.appendChild(cellAksi);

                tbody.appendChild(firstRow);

                // Additional Rows
                for (let t = 1; t < rows; t++) {
                    const additionalRow = document.createElement("tr");

                    const additionalCellJawabanBenar = document.createElement("td");
                    additionalCellJawabanBenar.innerHTML = formatJawaban(detail?.tabel_soal[t]);
                    additionalRow.appendChild(additionalCellJawabanBenar);

                    const additionalCellJawabanSiswa = document.createElement("td");
                    additionalCellJawabanSiswa.innerHTML = formatJawaban(detail?.tabel_jawab[t]);
                    additionalRow.appendChild(additionalCellJawabanSiswa);

                    tbody.appendChild(additionalRow);
                }
            });

            const totalRow = document.createElement("tr");
            const totalCell = document.createElement("td");
            totalCell.colSpan = 5;
            totalCell.className = "text-right align-middle text-bold";
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
            container.classList.add('card', 'card-default', 'my-shadow', 'mb-4','collapsed-card');

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
            collapseButton.setAttribute('data-card-widget', 'collapse');
            collapseButton.setAttribute('title', 'Collapse');
            collapseButton.innerHTML = '<i class="fas fa-plus"></i>';
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
            <li>Klik <i class="fa fa-pencil text-yellow"></i> untuk mengedit point. Klik <i class="fa fa-undo text-blue"></i> untuk mengembalikan ke point otomatis</li>
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

            soal_isian.forEach((soal) => {
                const detail = details_is.detail.find(detail => detail.id_soal === soal.id_soal);
                const row = document.createElement('tr');

                const noSoalCell = document.createElement('td');
                noSoalCell.classList.add('text-center');
                noSoalCell.textContent = soal?.nomor_soal;
                row.appendChild(noSoalCell);

                const soalCell = document.createElement('td');
                soalCell.innerHTML = soal?.soal;
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
                analisaCell.innerHTML = detail?.analisa;
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
                editButton.innerHTML = '<i class="fa fa-pencil text-yellow"></i>';
                const undoButton = document.createElement('button');
                undoButton.id = `undo${soal?.id_soal}`;
                undoButton.type = 'button';
                undoButton.classList.add('btn', 'btn-sm');
                undoButton.onclick = () => undo(soal?.id_soal, detail?.nilai_asli);
                undoButton.innerHTML = '<i class="fa fa-undo text-blue"></i>';
                aksiCell.appendChild(editButton);
                aksiCell.appendChild(undoButton);
                row.appendChild(aksiCell);

                tbody.appendChild(row);

                ns++;
            });

            const totalRow = document.createElement('tr');
            const totalLabelCell = document.createElement('td');
            totalLabelCell.colSpan = 5;
            totalLabelCell.classList.add('text-right', 'text-bold', 'align-middle');
            totalLabelCell.textContent = 'TOTAL SCORE ISIAN SINGKAT';
            totalRow.appendChild(totalLabelCell);

            const skorAsli = formatNilai(details_is.skor_asli)
            const skorTotal = formatNilai(details_is.skor_total)

            const totalScoreCell = document.createElement('td');
            totalScoreCell.classList.add('alert-default-primary','text-center', 'text-bold');
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
            cardHeader.classList.add('alert-default-success');
            const headerTitle = document.createElement('h6');
            headerTitle.classList.add('card-title');
            headerTitle.innerHTML = '<b>V. URAIAN</b>';
            const cardTools = document.createElement('div');
            cardTools.classList.add('card-tools');
            const collapseButton = document.createElement('button');
            collapseButton.type = 'button';
            collapseButton.classList.add('btn', 'btn-tool');
            collapseButton.setAttribute('data-card-widget', 'collapse');
            collapseButton.setAttribute('title', 'Collapse');
            collapseButton.innerHTML = '<i class="fas fa-plus"></i>';
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
            <li>Klik <i class="fa fa-pencil text-yellow"></i> untuk mengedit point. Klik <i class="fa fa-undo text-blue"></i> untuk mengembalikan ke point otomatis</li>
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

            soal_esai.forEach((soal) => {
                const detail = details_es.detail.find(detail => detail.id_soal === soal.id_soal);

                const row = document.createElement('tr');

                const noSoalCell = document.createElement('td');
                noSoalCell.classList.add('text-center');
                noSoalCell.textContent = soal?.nomor_soal;
                row.appendChild(noSoalCell);

                const soalCell = document.createElement('td');
                soalCell.innerHTML = soal?.soal;
                row.appendChild(soalCell);

                const jawabanBenarCell = document.createElement('td');
                jawabanBenarCell.classList.add('text-center');
                jawabanBenarCell.innerHTML = soal?.jawaban || '--';
                row.appendChild(jawabanBenarCell);

                const jawabanSiswaCell = document.createElement('td');
                jawabanSiswaCell.classList.add('text-center');
                jawabanSiswaCell.innerHTML = detail?.jawaban_siswa || '-';
                row.appendChild(jawabanSiswaCell);

                const analisaCell = document.createElement('td');
                analisaCell.classList.add('text-center');
                analisaCell.innerHTML = detail?.analisa || '<i class="fa fa-times-circle text-yellow text-lg"></i>';
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
                console.log('test', soal?.id_soal,detail?.skor_jawaban)
                pointCell.appendChild(input);
                pointCell.appendChild(span);
                row.appendChild(pointCell);

                const aksiCell = document.createElement('td');
                const editButton = document.createElement('button');
                editButton.id = `edit${soal?.id_soal}`;
                editButton.type = 'button';
                editButton.classList.add('btn', 'btn-sm');
                editButton.onclick = () => edit(soal?.id_soal, "essai");
                editButton.innerHTML = '<i class="fa fa-pencil text-yellow"></i>';

                const undoButton = document.createElement('button');
                undoButton.id = `undo${soal?.id_soal}`;
                undoButton.type = 'button';
                undoButton.classList.add('btn', 'btn-sm');
                undoButton.onclick = () => undo(soal?.id_soal, detail?.nilai_asli);
                undoButton.innerHTML = '<i class="fa fa-undo text-blue"></i>';
                aksiCell.appendChild(editButton);
                aksiCell.appendChild(undoButton);
                row.appendChild(aksiCell);

                tbody.appendChild(row);

                ns++;
            });

            const totalRow = document.createElement('tr');
            const totalLabelCell = document.createElement('td');
            totalLabelCell.colSpan = 5;
            totalLabelCell.classList.add('text-right', 'text-bold', 'align-middle');
            totalLabelCell.textContent = 'TOTAL SCORE URAIAN';
            totalRow.appendChild(totalLabelCell);

            const skorAsli = formatNilai(details_es.skor_asli)
            const skorTotal = formatNilai(details_es.skor_total)

            const totalScoreCell = document.createElement('td');
            totalScoreCell.classList.add('alert-default-primary','text-center', 'text-bold');
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
            btnedit.html(`<i class="fa fa-pencil text-yellow"></i>`);
            btnSave.removeAttr('disabled')
        } else {
            span.hide();
            input.val(span.text()).show();
            btnedit.html(`<i class="fa fa-check"></i>`);
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
            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
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
                        swal.fire({
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
                        swal.fire({
                            title: "Gagal",
                            text: 'Tidak ada nilai yang disimpan',
                            icon: "error"
                        });
                    }
                }, error: function (xhr, status, error) {
                    loading.addClass('d-none');
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        }
    }
</script>
