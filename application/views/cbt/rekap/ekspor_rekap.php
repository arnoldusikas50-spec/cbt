<?php



$XA = isset($convert) ? $convert['xa'] : 0;
$XB = isset($convert) ? $convert['xb'] : 20;
$YA = isset($convert) ? $convert['ya'] : 100;
$YB = isset($convert) ? $convert['yb'] : 60;

$colWidth = '';
function decimalFixed($num)
{
    return round(($num * 100) / 100, 2);
}

?>

<main class="app-main bg-white">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6">
                    <button onclick="window.history.back()" type="button" class="btn btn-sm btn-danger float-end">
                        <i class="bi bi-arrow-left-square"></i><span
                            class="d-none d-sm-inline-block ms-1">Kembali</span>
                        </>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools ms-auto" id="group-export"></div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 overflow-auto">
                            <table class="table table-borderless table-sm" id="table-status"></table>
                        </div>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <!-- <div class="spinner-grow"></div> -->
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                        Loading...
                    </button>
                </div>
            </div>
        </div>
    </section>
    </div>

    <div class="modal fade" id="perbaikanModal" tabindex="-1" role="dialog" aria-labelledby="perbaikanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="perbaikanModalLabel">Perbaikan Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <?= form_open('', array('id' => 'perbaikan-nilai')) ?>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label for="nama_sesi" class="col-md-4 col-form-label">Nilai Tertinggi</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="ya" value="<?= $YA ?>"
                                placeholder="Nilai tertinggi yang diinginkan" required>
                            <small>diisi nilai puluhan maksimal 100, misal 80 sampai 100</small>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="kode_sesi" class="col-md-4 col-form-label">Nilai Terrendah</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="yb" value="<?= $YB ?>"
                                placeholder="Nilai terrendah yang diinginkan" required>
                            <small>diisi nilai puluhan dibawah nilai tertinggi, misal 60</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="convert">Katrol <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/tableToExcel.js"></script>

    <script>
        var colWidthMin = '5,30,35,10,10,10';
        var colWidth = '<?= $colWidth ?>';

        var idJadwal = '<?= $jadwal_selected ?>';
        var idKelas = '<?= $kelas_selected ?>';
        var isSelected = <?= isset($siswa) ? '1' : '0' ?>;
        let namaMapel = '';
        let namaKelas = '';

        let nilai_max = 0;//nilai siswa terbesar
        let nilai_min = 100;//nilai siswa terkecil
        let hasil_max = 100;//batas nilai terbesar
        let hasil_min = 60;//batas nilai terkecil
        //let arrNilaiSiswa = []
        let isConverted = false
        let response;
        let exluded = false;

        function decimalFixed(num) {
            return Math.round(num * 100) / 100
        }

        function createRow(cells) {
            const row = document.createElement("tr");
            cells.forEach(([colspan, content, isBold = false]) => {
                const cell = document.createElement("td");
                if (colspan) cell.setAttribute("colspan", colspan);
                if (isBold) {
                    const boldContent = document.createElement("b");
                    boldContent.textContent = content;
                    cell.appendChild(boldContent);
                } else {
                    cell.textContent = content;
                }
                row.appendChild(cell);
            });
            return row;
        }

        // Fungsi untuk memformat tanggal singkat
        function singkatTanggal(tanggal) {
            const options = { day: "2-digit", month: "short", year: "numeric" };
            return new Date(tanggal).toLocaleDateString("id-ID", options);
        }

        function loadSiswaKelas() {
            var loading = $('#loading');
            loading.removeClass('d-none');
            let formData = new FormData($('#perbaikan-nilai')[0]);
            formData.append('id_kelas', idKelas)
            formData.append('id_jadwal', idJadwal)
            $.ajax({
                url: base_url + 'cbtrekap/get_nilai_rekap',
                data: formData,
                method: "POST",
                processData: false,
                contentType: false,
                success: function (data) {
                    loading.addClass('d-none');
                    console.log('res', data)
                    if (data.success) {
                        response = data;
                        populateTable(false)
                    } else {
                        loading.addClass('d-none');
                        Swal.fire({
                            title: "Gagal",
                            text: 'Tidak ada nilai',
                            icon: "error"
                        });
                    }
                },
                error: function (xhr, status, error) {
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

        function populateTable() {
            const rekap = response.rekap
            namaMapel = rekap.nama_mapel
            const groupExport = document.getElementById("group-export");
            while (groupExport.firstChild) {
                groupExport.removeChild(groupExport.firstChild);
            }
            const katrolButton = document.createElement("button");
            katrolButton.type = "button";
            katrolButton.id = 'btn-katrol';

            if (isConverted) {
                katrolButton.classList.add("btn", "btn-warning", "align-text-bottom", "m-1", "btn-sm");
            } else {
                katrolButton.classList.add("btn", "btn-danger", "align-text-bottom", "m-1", "btn-sm");
            }
            groupExport.appendChild(katrolButton);

            // Tombol Tampil Detail
            const detailButton = document.createElement("button");
            detailButton.type = "button";
            detailButton.id = "nilai-detail";
            detailButton.classList.add("btn", exluded ? "btn-warning" : "btn-primary", "align-text-bottom", "m-1", "btn-sm");
            detailButton.setAttribute("data-toggle", "tooltip");
            detailButton.setAttribute("title", "Tampil Detail");
            groupExport.appendChild(detailButton);

            // Tombol Download Excel
            const excelButton = document.createElement("button");
            excelButton.type = "button";
            excelButton.id = "download-excel";
            excelButton.classList.add("btn", "btn-success", "align-text-bottom", "m-1", "btn-sm");
            excelButton.setAttribute("data-toggle", "tooltip");
            excelButton.setAttribute("title", "Download Excel");
            groupExport.appendChild(excelButton);

            const rekapJadwal = rekap.bank_soal;
            rekapJadwal.tampil_urutan = rekap.soal_urutan ? rekap.soal_urutan.length : 0;
            rekapJadwal.tampil_cloze = rekap.soal_cloze ? rekap.soal_cloze.length : 0;

            const kelas = rekap.kelas_bank
            const getNamaKelas = () => {
                const kls = kelas.find(k => k.id_kelas === response.kelas_selected)
                return kls.nama_kelas ?? ''
            }
            namaKelas = getNamaKelas();

            if (rekapJadwal && kelas) {
                let colWidth = '5,15,35';
                for (let s = 0; s < rekapJadwal.tampil_pg; s++) {
                    colWidth += ',5';
                }
                colWidth += ',10,10,10';

                // Menghitung jumlah soal
                const jml_soal = Number(rekapJadwal.tampil_pg) +
                    Number(rekapJadwal.tampil_kompleks) +
                    Number(rekapJadwal.tampil_jodohkan) +
                    Number(rekapJadwal.tampil_isian) +
                    Number(rekapJadwal.tampil_esai) +
                    Number(rekapJadwal.tampil_urutan) +
                    Number(rekapJadwal.tampil_cloze);

                // Menghitung kolom yang terisi
                const cols = [
                    rekapJadwal.tampil_pg,
                    rekapJadwal.tampil_kompleks,
                    rekapJadwal.tampil_jodohkan,
                    rekapJadwal.tampil_isian,
                    rekapJadwal.tampil_esai,
                    rekapJadwal.tampil_urutan,
                    rekapJadwal.tampil_cloze
                ].filter(col => col > 0);
                //console.log('columns', cols)

                const rows = rekapJadwal.tampil_pg > 0 || cols.length > 1 ? 2 : 1;

                const table = document.getElementById("table-status");
                while (table.firstChild) {
                    table.removeChild(table.firstChild);
                }
                table.id = 'table-status';
                table.style.fontSize = '10pt';
                table.style.borderRight = '1px solid #000';
                table.setAttribute("data-cols-width", exluded ? colWidthMin : colWidth);

                // Menambahkan baris pertama
                table.appendChild(
                    createRow([
                        [2, "Soal"],
                        [1, rekapJadwal.bank_kode, true],
                        [10, "Mata Pelajaran"],
                        [15, rekap.nama_mapel, true]
                    ])
                );

                // Menambahkan baris kedua
                table.appendChild(
                    createRow([
                        [2, "Jenis Penilaian"],
                        [1, rekap.kode_jenis, true],
                        [10, "Guru Pengampu"],
                        [15, rekap.nama_guru, true]
                    ])
                );

                // Menambahkan baris ketiga
                table.appendChild(
                    createRow([
                        [2, "Kelas"],
                        [1, namaKelas, true],
                        [10, "Waktu Pelaksanaan"],
                        [15, `${singkatTanggal(rekap.tgl_mulai)} ~ ${singkatTanggal(rekap.tgl_selesai)}`, true]
                    ])
                );

                // Menambahkan baris keempat
                table.appendChild(
                    createRow([
                        [2, "Jumlah Soal"],
                        [1, jml_soal, true],
                        [10, "TP / SMT"],
                        [15, `${rekap.tp} smt ${rekap.smt}`, true]
                    ])
                );

                // Menambahkan baris kosong
                table.appendChild(createRow([[null, ""]]));

                const siswas = rekap.rekapsiswa
                if (siswas) {
                    // Create header rows
                    const headerRow1 = document.createElement('tr');
                    const headerRow2 = document.createElement('tr');

                    const headers = [
                        { text: "No.", rowspan: rows, width: 40 },
                        { text: "No. Peserta", rowspan: rows, width: 220 },
                        { text: "Nama", rowspan: rows, 'class': 'nama-siswa' }
                    ];

                    const paramExcel = [
                        { "data": "data-fill-color", "value": "d8eafd" },
                        { "data": "data-a-v", "value": "middle" },
                        { "data": "data-a-h", "value": "center" },
                        { "data": "data-b-a-s", "value": "thin" },
                        { "data": "data-f-bold", "value": "true" }]

                    headers.forEach(header => {
                        const th = document.createElement('th');
                        th.classList.add("text-center", "align-middle", "bg-gray-light");
                        if (header.class) {
                            th.classList.add(header.class)
                            //th.colSpan = '2'
                        }
                        th.rowSpan = header.rowspan;
                        th.textContent = header.text;
                        th.style.border = '1px solid black';
                        for (const param of paramExcel) {
                            th.setAttribute(param.data, param.value)
                        }
                        if (header.width) th.style.minWidth = `${header.width}px`;
                        headerRow1.appendChild(th);
                    });

                    if (rekapJadwal.tampil_pg > 0) {
                        const pgHeader = document.createElement('th');
                        pgHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                        pgHeader.setAttribute('data-exclude', exluded)
                        if (exluded) {
                            pgHeader.classList.add("d-none")
                        }
                        pgHeader.setAttribute('id', 'no-soal-tile')
                        pgHeader.colSpan = rekapJadwal.tampil_pg;
                        pgHeader.textContent = "Nomor Soal";
                        //pgHeader.style.textAlign = 'center';
                        pgHeader.style.border = '1px solid black';
                        headerRow1.appendChild(pgHeader);

                        const pgBenarHeader = document.createElement('th');
                        pgBenarHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                        pgBenarHeader.rowSpan = 2;
                        pgBenarHeader.textContent = "PG Benar";
                        //pgBenarHeader.style.textAlign = 'center';
                        pgBenarHeader.style.border = '1px solid black';
                        headerRow1.appendChild(pgBenarHeader);

                        for (const param of paramExcel) {
                            pgHeader.setAttribute(param.data, param.value)
                            pgBenarHeader.setAttribute(param.data, param.value)
                        }

                        let num_soal = 1;
                        for (const soalPg of rekap.soal_pg) {
                            const th = document.createElement('th');
                            th.classList.add('text-center', 'align-middle', 'bg-gray-light', 'p-1', 'no-soal')
                            if (exluded) {
                                th.classList.add("d-none")
                            }
                            th.setAttribute('data-exclude', exluded)
                            th.textContent = num_soal;
                            th.style.minWidth = '40px';
                            th.style.border = '1px solid black';
                            for (const param of paramExcel) {
                                th.setAttribute(param.data, param.value)
                            }
                            headerRow2.appendChild(th);
                            num_soal++;
                        }
                    }

                    const scoreTypes = [
                        { tampil: 'tampil_pg', text: 'PG' },
                        { tampil: 'tampil_kompleks', text: 'PGK' },
                        { tampil: 'tampil_jodohkan', text: 'Jod' },
                        { tampil: 'tampil_isian', text: 'Is' },
                        { tampil: 'tampil_esai', text: 'Es' },
                        { tampil: 'tampil_urutan', text: 'Urt' },
                        { tampil: 'tampil_cloze', text: 'Clz' },
                    ];
                    scoreTypes.forEach(type => {
                        if (rekapJadwal[type.tampil] > 0) {
                            const th = document.createElement('th');
                            th.classList.add("text-center", "align-middle", "bg-gray-light");
                            th.textContent = type.text;
                            //th.style.textAlign = 'center';
                            th.style.border = '1px solid black';
                            for (const param of paramExcel) {
                                th.setAttribute(param.data, param.value)
                            }
                            headerRow2.appendChild(th);
                        }
                    });
                    const skorAsli = document.createElement('th');
                    skorAsli.classList.add("text-center", "align-middle", "bg-gray-light");
                    skorAsli.textContent = "Asli";
                    //skorAsli.style.textAlign = 'center';
                    skorAsli.style.border = '1px solid black';
                    headerRow2.appendChild(skorAsli);

                    const nilaiKatrol = document.createElement('th');
                    nilaiKatrol.classList.add("text-center", "align-middle", "bg-gray-light");
                    nilaiKatrol.textContent = "Katrol";
                    //nilaiKatrol.style.textAlign = 'center';
                    nilaiKatrol.style.border = '1px solid black';
                    headerRow2.appendChild(nilaiKatrol);

                    // Add SKOR and NILAI columns
                    const skorHeader = document.createElement('th');
                    skorHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                    skorHeader.colSpan = cols.length;
                    skorHeader.textContent = "SKOR";
                    skorHeader.style.border = '1px solid black';
                    headerRow1.appendChild(skorHeader);

                    const nilaiHeader = document.createElement('th');
                    nilaiHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                    nilaiHeader.colSpan = 2;
                    nilaiHeader.textContent = "Nilai";
                    nilaiHeader.style.border = '1px solid black';
                    headerRow1.appendChild(nilaiHeader);

                    for (const param of paramExcel) {
                        skorAsli.setAttribute(param.data, param.value)
                        nilaiKatrol.setAttribute(param.data, param.value)
                        skorHeader.setAttribute(param.data, param.value)
                        nilaiHeader.setAttribute(param.data, param.value)
                    }

                    table.appendChild(headerRow1);
                    table.appendChild(headerRow2);

                    let no = 1;

                    const paramNumName = [
                        { "data": "data-a-v", "value": "middle" },
                        { "data": "data-b-a-s", "value": "thin" }
                    ]

                    const paramAll = [
                        { "data": "data-a-v", "value": "middle" },
                        { "data": "data-a-h", "value": "center" },
                        { "data": "data-b-a-s", "value": "thin" }
                    ]
                    // Populate student rows
                    if (siswas.length > 0) {
                        siswas.forEach(siswa => {
                            const tr = document.createElement('tr');

                            const fields = [
                                { value: no, align: 'center' },
                                { value: siswa.no_peserta, align: 'center' },
                                { value: siswa.nama_siswa, overflow: 'hidden', whiteSpace: 'nowrap', 'class': 'nama-siswa' }
                            ];

                            fields.forEach(field => {
                                const td = document.createElement('td');
                                if (field.class) {
                                    td.classList.add(field.class)
                                } else {
                                    td.setAttribute("data-a-h", "center")
                                }
                                td.textContent = field.value;
                                if (field.align) td.style.textAlign = field.align;
                                if (field.overflow) td.style.overflow = field.overflow;
                                if (field.whiteSpace) td.style.whiteSpace = field.whiteSpace;
                                td.style.border = '1px solid grey';
                                for (const param of paramNumName) {
                                    td.setAttribute(param.data, param.value)
                                }
                                tr.appendChild(td);
                            });

                            if (rekap.soal_pg.length > 0) {
                                let benarPg = 0;
                                for (const soalPg of rekap.soal_pg) {
                                    const td = document.createElement('td');
                                    td.classList.add('no-soal-siswa')
                                    if (exluded) {
                                        td.classList.add("d-none")
                                    }
                                    td.setAttribute('data-exclude', exluded)
                                    //console.log('soalPg', soalPg)
                                    const soalSiswa = siswa.nilai_akhir?.skor_pg?.detail?.find(s => s.id_soal === soalPg.id_soal)
                                    //console.log('soalSiswa', siswa.nilai_akhir?.skor_pg?.detail)
                                    if (soalSiswa) {
                                        td.textContent = soalSiswa.jawaban_siswa;
                                        td.style.backgroundColor = soalSiswa.benar ? '#14e37e' : '#f69273';
                                        td.setAttribute("data-fill-color", soalSiswa.benar ? '14e37e' : 'f69273')
                                        if (soalSiswa.benar) benarPg++;
                                    } else {
                                        td.textContent = '-';
                                        td.style.backgroundColor = '#f69273';
                                        td.setAttribute("data-fill-color", 'f69273')
                                    }
                                    for (const param of paramAll) {
                                        td.setAttribute(param.data, param.value)
                                    }
                                    td.style.border = '1px solid #000';
                                    td.style.textAlign = 'center';
                                    tr.appendChild(td);
                                }

                                //soalPg.forEach((detail) => {});
                                const pgScore = document.createElement('td');
                                pgScore.textContent = benarPg.toString();
                                pgScore.style.textAlign = 'center';
                                pgScore.style.border = '1px solid #000';
                                for (const param of paramAll) {
                                    pgScore.setAttribute(param.data, param.value)
                                }
                                tr.appendChild(pgScore);
                            }

                            // Add other scores
                            const scoreTypes = [
                                { tampil: 'tampil_pg', text: 'PG', skor: siswa.nilai_akhir?.skor_pg?.skor_total },
                                { tampil: 'tampil_kompleks', text: 'PGK', skor: siswa.nilai_akhir?.skor_kompleks?.skor_total },
                                { tampil: 'tampil_jodohkan', text: 'Jod', skor: siswa.nilai_akhir?.skor_jodohkan?.skor_total },
                                { tampil: 'tampil_isian', text: 'Is', skor: siswa.nilai_akhir?.skor_isian?.skor_total },
                                { tampil: 'tampil_essai', text: 'Es', skor: siswa.nilai_akhir?.skor_essai?.skor_total },
                                { tampil: 'tampil_urutan', text: 'Urt', skor: siswa.nilai_akhir?.skor_urutan?.skor_total },
                                { tampil: 'tampil_cloze', text: 'Clz', skor: siswa.nilai_akhir?.skor_cloze?.skor_total },
                            ];
                            scoreTypes.forEach(type => {
                                if (Number(rekapJadwal[type.tampil]) > 0) {
                                    const td = document.createElement('td');
                                    td.textContent = type.skor || '0';
                                    td.style.textAlign = 'center';
                                    td.style.border = '1px solid #000';
                                    for (const param of paramAll) {
                                        td.setAttribute(param.data, param.value)
                                    }
                                    tr.appendChild(td);
                                }
                            });

                            const pel = siswa?.pelanggaran
                            const totalMinus = rekap?.jadwal?.pelanggaran === '1' ? (pel?.t_total || 0) : 0
                            const textColor = rekap?.jadwal?.pelanggaran === '1' && pel?.t_total != null && pel?.t_total > 0 ? 'text-danger' : 'text-black';

                            // Add total score and converted score
                            const skorTotal = (siswa.nilai_akhir?.skor_total || 0) - totalMinus

                            // katrol
                            let skorKatrol = '';
                            if (isConverted) {
                                //if (skorTotal > 0)
                                skorKatrol = (((hasil_max - hasil_min) / 100) * Number(skorTotal)) + hasil_min;
                                skorKatrol = Number(skorKatrol).toFixed(2);
                            } else {
                                const val = Number(skorTotal);
                                if (val > nilai_max) {
                                    nilai_max = val;
                                }
                                if (val < nilai_min) {
                                    nilai_min = val;
                                }
                            }

                            const totalScore = document.createElement('td');
                            totalScore.classList.add(textColor)
                            totalScore.textContent = skorTotal;
                            totalScore.style.textAlign = 'center';
                            totalScore.style.backgroundColor = 'yellow';
                            totalScore.style.border = '1px solid #000';
                            totalScore.style.minWidth = '50px';

                            const convertScore = document.createElement('td');
                            convertScore.classList.add(textColor)
                            // convertScore.classList.add('katrol');
                            convertScore.innerHTML = skorKatrol;
                            convertScore.style.backgroundColor = '#ffffff';
                            convertScore.style.textAlign = 'center';
                            convertScore.style.border = '1px solid #000';
                            convertScore.style.setProperty('border', '1px solid #000', 'important');
                            convertScore.style.minWidth = '50px';
                            convertScore.style.outline = '1px solid #000';

                            for (const param of paramAll) {
                                totalScore.setAttribute(param.data, param.value)
                                convertScore.setAttribute(param.data, param.value)
                            }
                            tr.appendChild(totalScore);
                            tr.appendChild(convertScore);

                            table.appendChild(tr);
                            no++;
                        });
                    } else {
                        const tr = document.createElement('tr');
                        const empty = document.createElement('td');
                        empty.classList.add('bg-warning')
                        empty.colSpan = Number(rekapJadwal.tampil_pg) + cols.length + 6;
                        empty.textContent = 'Belum ada rekap nilai siswa';
                        empty.style.textAlign = 'center';
                        empty.style.border = '1px solid grey';
                        tr.appendChild(empty);
                        table.appendChild(tr);
                    }

                    const analysis = [
                        { text: "Tingkat Kesulitan", analisa: 'tingkat_kesulitan' },
                        { text: "Daya Pembeda", analisa: 'daya_pembeda' },
                        { text: "Validitas", analisa: 'validitas' },
                        { text: "Indeks Kesukaran Distraktor (IKD Total)", analisa: 'distraktor' },
                        // todo elemen analisis
                        //{ text: "Efektivitas Distraktor", analisa: 'efektivitas'},
                        //{ text: "Proporsi Jawaban Benar", analisa: 'proporsi'},
                        //{ text: "Pola Jawaban", analisa: 'pola'},
                        //{ text: "Rasio Korelasi", analisa: 'korelasi'},
                        //{ text: "Tingkat Ketepatan Soal", analisa: 'ketepatan'},
                        //{ text: "DIF (Differential Item Functioning)", analisa: 'dif'},
                        //{ text: "Distribusi Skor", analisa: 'distribusi'},
                        //{ text: "Konsistensi Antarkelompok", analisa: 'konsistensi'},
                        { text: "Reliabilitas", cols: 'pg', analisa: 'reliabilitas' }
                    ]

                    analysis.forEach((analis, ind) => {
                        const row = document.createElement("tr");
                        if (ind === 0) {
                            const cell1 = document.createElement("td");
                            cell1.classList.add('text-bold', 'text-center', 'align-middle');
                            cell1.setAttribute('rowspan', '5')
                            cell1.textContent = 'ANALISIS';
                            cell1.style.border = '1px solid #000';
                            cell1.style.transform = 'rotate(-90deg)';
                            cell1.style.maxWidth = '30px';
                            cell1.style.whiteSpace = 'nowrap'
                            for (const param of paramAll) {
                                cell1.setAttribute(param.data, param.value)
                            }
                            cell1.setAttribute("data-a-text-rotation", "90")
                            cell1.setAttribute("data-f-bold", "true")

                            row.appendChild(cell1)
                        }

                        const cell2 = document.createElement("td");
                        cell2.classList.add('text-bold');
                        cell2.setAttribute('colspan', '2')
                        cell2.textContent = analis.text;
                        cell2.style.border = '1px solid #000';
                        for (const param of paramNumName) {
                            cell2.setAttribute(param.data, param.value)
                        }
                        cell2.setAttribute("data-f-bold", "true")
                        row.appendChild(cell2)

                        //const pgNilai = siswas.find(sw => sw)
                        const isValid = false;
                        if (rekapJadwal.tampil_pg > 0) {
                            if (analis.cols) {
                                const td = document.createElement('td');
                                td.setAttribute('colspan', rekapJadwal.tampil_pg)
                                td.classList.add('text-bold', 'no-soal-siswa', 'text-center')
                                if (exluded) {
                                    td.classList.add("d-none")
                                }
                                td.setAttribute('data-exclude', exluded)
                                td.textContent = response.analisis.reliabilitas.kr20 + ' = ' + response.analisis.reliabilitas.ket;
                                td.style.border = '1px solid #000';
                                for (const param of paramAll) {
                                    td.setAttribute(param.data, param.value)
                                }
                                td.setAttribute("data-f-bold", "true")
                                row.appendChild(td);
                            } else {
                                for (const idSoal in response.kunci) {
                                    const td = document.createElement('td');
                                    td.classList.add('text-bold', 'no-soal-siswa', 'text-center', 'text-xs')
                                    if (exluded) {
                                        td.classList.add("d-none")
                                    }
                                    td.setAttribute('data-exclude', exluded)
                                    if (analis.analisa === 'distraktor') {
                                        const kunci = response.kunci[idSoal]
                                        td.textContent = response.analisis[idSoal][analis.analisa][kunci]?.IKD_total
                                    } else {
                                        td.textContent = response.analisis[idSoal][analis.analisa];
                                    }
                                    //td.style.backgroundColor = isValid ? '#14e37e' : '#f69273';
                                    td.style.border = '1px solid #000';
                                    for (const param of paramAll) {
                                        td.setAttribute(param.data, param.value)
                                    }
                                    td.setAttribute("data-f-bold", "true")
                                    //td.setAttribute("data-fill-color", isValid ? '14e37e' : 'f69273')
                                    row.appendChild(td);
                                }
                            }

                            const pgAnalisa = document.createElement('td');
                            pgAnalisa.classList.add('text-bold');
                            pgAnalisa.textContent = '';
                            pgAnalisa.style.textAlign = 'center';
                            pgAnalisa.style.border = '1px solid #000';
                            for (const param of paramAll) {
                                pgAnalisa.setAttribute(param.data, param.value)
                            }
                            pgAnalisa.setAttribute("data-f-bold", "true")
                            row.appendChild(pgAnalisa);
                        }

                        const scoreTypes = [
                            { tampil: 'tampil_pg' },
                            { tampil: 'tampil_kompleks' },
                            { tampil: 'tampil_jodohkan' },
                            { tampil: 'tampil_isian' },
                            { tampil: 'tampil_essai' },
                            { tampil: 'tampil_urutan' },
                            { tampil: 'tampil_cloze' },
                        ];
                        scoreTypes.forEach(type => {
                            if (Number(rekapJadwal[type.tampil]) > 0) {
                                const td = document.createElement('td');
                                td.classList.add('text-bold');
                                td.textContent = '';
                                td.style.textAlign = 'center';
                                td.style.border = '1px solid #000';
                                for (const param of paramAll) {
                                    td.setAttribute(param.data, param.value)
                                }
                                td.setAttribute("data-f-bold", "true")
                                row.appendChild(td);
                            }
                        });

                        for (let i = 0; i < 2; i++) {
                            const td = document.createElement('td');
                            td.classList.add('text-bold');
                            td.innerHTML = '&nbsp;';
                            td.style.textAlign = 'center';
                            td.style.border = '1px solid #000';
                            if (i === 1) {
                                td.style.setProperty('border', '1px solid #000', 'important');
                                td.style.outline = '1px solid #000';
                            }
                            for (const param of paramAll) {
                                td.setAttribute(param.data, param.value)
                            }
                            td.setAttribute("data-f-bold", "true")
                            row.appendChild(td);
                        }
                        table.appendChild(row)
                    })

                    const rowSpace = document.createElement("tr");
                    const cellSpace = document.createElement("td");
                    cellSpace.textContent = '';
                    rowSpace.appendChild(cellSpace)
                    table.appendChild(rowSpace)

                    const analysisInfo = [
                        { text: "KETERANGAN:" },
                        {
                            text: "* Tingkat Kesulitan",
                            info: [
                                { value: '> 0.70', text: 'mudah' },
                                { value: '0.30 ~ 0.70', text: 'sedang' },
                                { value: '< 0.30', text: 'sulit' },
                            ]
                        },
                        {
                            text: "* Daya Pembeda",
                            info: [
                                { value: '> 0.40', text: 'baik' },
                                { value: '0.20 ~ 0.40', text: 'cukup' },
                                { value: '< 0.20', text: 'buruk' },
                            ]
                        },
                        {
                            text: "* Validitas",
                            info: [
                                { value: '> 0.70', text: 'sangat valid' },
                                { value: '0.30 ~ 0.70', text: 'valid' },
                                { value: '< 0.30', text: 'tidak valid' },
                            ]
                        },
                        /*
                        { text: "* Indeks Distraktor",
                            info: [
                                {value: '3', text: 'mudah'},
                                {value: '2', text: 'sedang'},
                                {value: '1', text: 'sulit'},
                            ]
                        },
                        // todo elemen analisis
                        //{ text: "Efektivitas Distraktor", analisa: 'efektivitas'},
                        //{ text: "Proporsi Jawaban Benar", analisa: 'proporsi'},
                        //{ text: "Pola Jawaban", analisa: 'pola'},
                        //{ text: "Rasio Korelasi", analisa: 'korelasi'},
                        //{ text: "Tingkat Ketepatan Soal", analisa: 'ketepatan'},
                        //{ text: "DIF (Differential Item Functioning)", analisa: 'dif'},
                        //{ text: "Distribusi Skor", analisa: 'distribusi'},
                        //{ text: "Konsistensi Antarkelompok", analisa: 'konsistensi'},
                        { text: "* Reliabilitas",
                            info: [
                                {value: '3', text: 'mudah'},
                                {value: '2', text: 'sedang'},
                                {value: '1', text: 'sulit'},
                            ]
                        }
                         */
                    ]

                    analysisInfo.forEach((info, ind) => {
                        const rowKet = document.createElement("tr");
                        const cellKet = document.createElement("td");
                        cellKet.setAttribute('colspan', '3')
                        if (ind === 0) cellKet.classList.add('text-bold');
                        cellKet.textContent = info.text;
                        rowKet.appendChild(cellKet)
                        if (info.info) {
                            for (const inf of info.info) {
                                const cellVal = document.createElement("td");
                                cellVal.setAttribute('colspan', '3')
                                cellVal.textContent = inf.value;
                                rowKet.appendChild(cellVal)
                                const cellTxt = document.createElement("td");
                                cellTxt.setAttribute('colspan', '4')
                                cellTxt.textContent = ": " + inf.text;
                                rowKet.appendChild(cellTxt)
                            }
                        }
                        table.appendChild(rowKet)
                    })
                } else {
                    const tr = document.createElement('tr');
                    const empty = document.createElement('td');
                    empty.id = 'empty'
                    empty.textContent = 'Belum ada rekap nilai siswa';
                    empty.style.textAlign = 'center';
                    empty.style.border = '1px solid grey';
                    tr.appendChild(empty);
                    table.appendChild(tr);
                }

                if (isConverted) {
                    $('#btn-katrol').html('<i class="bi bi-undo ms-1 me-1"></i>' +
                        '<span class="d-none d-sm-inline-block ms-1"> Nilai Asli</span>')
                } else {
                    $('#btn-katrol').html('<i class="bi bi-balance-scale-right ms-1 me-1"></i>' +
                        '<span class="d-none d-sm-inline-block ms-1"> Katrol</span>')
                }

                $('#nilai-detail').html('<i class="bi bi-list ms-1 me-1"></i>' +
                    '<span class="d-none d-sm-inline-block ms-1"> Tampil Detail</span>')

                $('#download-excel').html('<i class="bi bi-file-excel ms-1 me-1"></i>' +
                    '<span class="d-none d-sm-inline-block ms-1"> Ekspor ke Excel</span>')

                $('#btn-katrol').on('click', function (e) {
                    $(this).toggleClass('btn-danger')
                    $(this).toggleClass('btn-warning')
                    perbaikiNilai(false)
                });

                $("#nilai-detail").click(function (event) {
                    $(this).toggleClass('btn-success')
                    $(this).toggleClass('btn-warning')
                    $("#no-soal-tile").toggleClass('d-none');
                    $(".no-soal").toggleClass('d-none');
                    $(".no-soal-siswa").toggleClass('d-none');

                    exluded = !exluded;
                    //$('#empty').attr('colspan', 2);
                    $('#no-soal-tile').attr('data-exclude', '' + exluded);
                    $('.no-soal').attr('data-exclude', '' + exluded);
                    $('.no-soal-siswa').attr('data-exclude', '' + exluded);
                    $('#table-status').attr('data-cols-width', exluded ? colWidthMin : colWidth)
                });

                $("#download-excel").click(function (event) {
                    var table = document.querySelector("#table-status");
                    TableToExcel.convert(table, {
                        name: `Rekap Nilai${exluded ? '' : ' Detail'} Kelas ${namaKelas} ${namaMapel}.xlsx`,
                        sheet: {
                            name: "Nilai"
                        }
                    });
                });

            }
        }

        function perbaikiNilai(fromModal) {
            if (!isConverted && !fromModal) {
                $('#perbaikanModal').modal('show')
                return
            }
            isConverted = !isConverted;
            populateTable()
        }

        $(document).ready(function () {
            ajaxcsrf();
            $('#perbaikan-nilai').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var table = document.querySelector("#table-status");
                var $inputs = $('#perbaikanModal :input');
                var values = {};
                $inputs.each(function () {
                    if ($(this).val()) values[this.name] = $(this).val();
                });
                hasil_max = Number(values['ya']);
                hasil_min = Number(values['yb']);
                console.log(values)

                $('#perbaikanModal').modal('hide').data('bs.modal', null);
                $('#perbaikanModal').on('hidden', function () {
                    $(this).data('modal', null);  // destroys modal
                });

                perbaikiNilai(true)

            });

            setTimeout(function () {
                loadSiswaKelas();
            }, 300)
        })
    </script>