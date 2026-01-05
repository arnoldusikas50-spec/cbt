<main class="app-main bg-white">
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
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title"><b><?= $subjudul ?></b></h3>
                    <div class="btn-group card-tools ms-auto">
                        <a href="<?= base_url('cbtalokasi') ?>" type="button" class="btn btn-sm btn-primary">By
                            Mapel</a>
                        <a href="#" type="button" class="btn btn-sm btn-primary active">Detail</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-default-info shadow align-content-center" role="alert">
                        <strong>Penting!</strong>
                        <ol>
                            <li>
                                Mengatur urutan aktif jadwal ujian sesuai urutan jam
                            </li>
                            <li>
                                Contoh <b>Jam ke</b> 2, maka jadwal mapel akan aktif setelah jam pertama selesai
                                dikerjakan oleh siswa
                            </li>
                            <li>
                                Jika semua <b>Jam ke</b> diatur ke 0 atau 1 maka semua jadwal yang aktif akan bisa
                                dikerjakan oleh siswa
                            </li>
                            <li>
                                Halaman ini berlaku jika semua jadwal telah diaktifkan
                            </li>
                            <li>
                                Jika halaman ini tidak diatur/tidak disimpan, maka jadwal ujian mengikuti aturan di MENU
                                <b>JADWAL</b>
                            </li>
                        </ol>
                    </div>
                    <br>
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="mb-3">
                                <label for="jenis">Jenis</label>
                                <?php
                                echo form_dropdown('jenis', $jenis, null, 'id="jenis" class="form-control"'); ?>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="mb-3">
                                <label for="jenis">Tanggal</label>
                                <select name="tanggal" id="tanggal" class="form-control"></select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table-container"></div>
                    <?= form_open('', array('id' => 'savealokasi')) ?>
                    <button type="submit" class="btn btn-primary card-tools mb-3 mt-3 float-end d-none ms-auto">
                        <i class="bi bi-save me-2"></i>Simpan
                    </button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </section>
    </div>
    <script src="<?= base_url() ?>/assets/app/js/jquery.rowspanizer.js"></script>
    <script>
        const id_tp = '<?= $tp_active->id_tp ?>';
        const id_smt = '<?= $smt_active->id_smt ?>';
        $(document).ready(async function () {
            ajaxcsrf();
            var opsiJenis = $("#jenis");
            var opsiTgl = $("#tanggal");
            opsiTgl.prepend("<option value=''>Pilih Tanggal</option>");

            opsiJenis.change(function () {
                opsiTgl.empty();
                opsiTgl.prepend("<option value=''>Pilih Tanggal</option>");
                getTglJadwal();
            });

            opsiTgl.change(function () {
                getAllJadwal();
            });

            function getTglJadwal() {
                var jenis = opsiJenis.val();
                var kosong = jenis == "";
                var url = base_url + 'cbtalokasi/get_tanggal?jenis=' + jenis;
                if (!kosong) {
                    $.ajax({
                        url: url,
                        type: "GET",
                        success: function (respon) {
                            console.log(respon)
                            for (const tgl in respon) {
                                const mulai = new Date(tgl).toLocaleDateString('id-ID', {
                                    year: "numeric",
                                    month: "long",
                                    day: "2-digit",
                                })

                                console.log(mulai)
                                opsiTgl.append(
                                    `<option value="${tgl}">${mulai}</option>`
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                        }
                    });
                }
            }

            function getAllJadwal() {
                var jenis = opsiJenis.val();
                if (jenis) {
                    let formData = new FormData($('#savealokasi')[0]);
                    formData.append('jenis', jenis)
                    formData.append('tanggal', opsiTgl.val())

                    Swal.fire({
                        title: "Memuat data jadwal",
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: base_url + 'cbtalokasi/get_alokasi',
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: async function (respon) {
                            await createTableFromJSON(respon);
                            setTimeout(() => {
                                swal.close();
                            }, 500);
                        },
                        error: function (xhr, status, error) {
                        }
                    });
                }
            }

            $('#savealokasi').on('submit', function (e) {
                e.stopPropagation();
                e.preventDefault();
                e.stopImmediatePropagation();

                var jenis = opsiJenis.val();
                var kosong = jenis == ""; //ruang == '' || sesi == "" ||
                if (kosong) return;

                Swal.fire({
                    title: "Menyimpan alokasi waktu ujian",
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const tables = $('#table-alokasi');
                var jsonObj = [];
                const $rows1 = $(tables).find('tr'), headers1 = $rows1.splice(0, 1);
                $rows1.each((i, row) => {
                    const jam_ke = $(row).find('input[name="jamke"]').val();
                    const ruang = $(row).find('.jadwal').data('ruang');
                    const sesi = $(row).find('.jadwal').data('sesi');
                    const idJadwals = ($(row).find('.jadwal').data('id')).toString();
                    const jadwals = idJadwals?.split(',');
                    for (const jadwal of jadwals) {
                        const id_pengawas = id_tp + '' + id_smt + '' + jadwal + '' + ruang + '' + sesi;
                        let item = {};
                        item["id_pengawas"] = id_pengawas;
                        item["id_jadwal"] = jadwal;
                        item["id_sesi"] = sesi;
                        item["id_tp"] = id_tp;
                        item["id_smt"] = id_smt;
                        item["id_ruang"] = ruang;
                        item["jam_ke"] = jam_ke;

                        jsonObj.push(item);
                    }
                });

                console.log('table', jsonObj);
                var dataPost = $(this).serialize() + "&alokasi=" + JSON.stringify(jsonObj);

                setTimeout(() => {
                    $.ajax({
                        url: base_url + "cbtalokasi/savealokasi",
                        type: "POST",
                        dataType: "JSON",
                        data: dataPost,
                        success: function (data) {
                            console.log("response:", data);
                            if (data.status) {
                                Swal.fire({
                                    title: "Sukses",
                                    html: "Alokasi waktu berhasil disimpan",
                                    icon: "success",
                                    showCancelButton: false,
                                    confirmButtonColor: "#3085d6",
                                    confirmButtonText: "OK"
                                }).then(result => {
                                    if (result.value) {
                                        //window.location.reload();
                                        getAllJadwal()
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "Info",
                                    text: "Tidak ada data yang disimpan",
                                    icon: "info"
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("response:", xhr.responseText);
                            const err = JSON.parse(xhr.responseText)
                            Swal.fire({
                                title: "Error",
                                text: err.Message,
                                icon: "error"
                            });
                        }
                    });
                }, 1000);
            });

            opsiJenis.val('').trigger('change');
        })

        /**
         * Main function to create and populate the exam schedule table from JSON data.
         * @param {object} data - The JSON data containing exam schedules, rooms, and supervisors.
         */
        async function createTableFromJSON(data) {
            const tableContainer = document.getElementById('table-container');
            const allSesi = data.ruangs || [];
            const jsonData = data.jadwals_ujian || [];

            // 1. Validate and handle empty data
            if (!validateData(allSesi, jsonData, tableContainer)) {
                return;
            }

            // 2. Process and group the data
            //const flatSchedules = processDataToFlatList(jsonData, allSesi, data);
            const groupedSchedules = processDataToFlatList(data) //processAndGroupData(data);
            console.log('group', groupedSchedules)

            // 3. Sort the final list for correct rowspans
            /*
            groupedSchedules.sort((a, b) => {
                if (a.ruang_sesi.ruang_id !== b.ruang_sesi.ruang_id) return a.ruang_sesi.ruang_id - b.ruang_sesi.ruang_id;
                return a.ruang_sesi.sesi_id - b.ruang_sesi.sesi_id;
            });
             */
            groupedSchedules.sort((a, b) => {
                if (a.ruang_id !== b.ruang_id) return a.ruang_id - b.ruang_id;
                return a.sesi_id - b.sesi_id;
            });

            // 4. Calculate rowspans for 'ruang' and 'sesi' columns
            const rowCounts = calculateRowCounts(groupedSchedules, 'ruang_id');
            const sesiRowCounts = calculateRowCounts(groupedSchedules, 'ruang_id', 'sesi_id');

            console.log('rowCounts', rowCounts);
            console.log('sesiRowCounts', sesiRowCounts);

            // 5. Build and render the HTML table
            tableContainer.innerHTML = buildTable(groupedSchedules, data, rowCounts, sesiRowCounts);

            // 6. Initialize plugins and UI elements
            initializeUI();
        }

        /**
         * Validates if the necessary data arrays are not empty.
         * @param {Array} allSesi - The list of rooms.
         * @param {Array} jsonData - The list of exam schedules.
         * @param {HTMLElement} container - The table container element.
         * @returns {boolean} - True if data is valid, false otherwise.
         */
        function validateData(allSesi, jsonData, container) {
            if (allSesi.length === 0 || jsonData.length === 0) {
                container.innerHTML = `<table class="w-100 table-bordered"><tr><td class="text-center bg-warning p-2">Tidak ada jadwal penilaian hari ini</td></tr></table>`;
                return false;
            }
            return true;
        }

        /**
         * Processes and creates a flat list of schedules for each room.
         * @param {object} data - The full data object.
         * @returns {Array} - A flat list where each item represents one schedule in one room.
         */
        function processDataToFlatList(data) {
            const allSesi = data.ruangs || [];
            const jsonData = data.jadwals_ujian || [];
            const flatList = [];

            jsonData.forEach(jadwal => {
                jadwal.bank.bank_kelas = jadwal.bank.bank_kelas.map(String)
                const ruangFiltered = allSesi.filter(r =>
                    r.kelas_id.some(id => jadwal.bank.bank_kelas.includes(id?.toString()))
                );

                ruangFiltered.forEach(ruang => {
                    const pengawasJadwal = data.pengawas[jadwal.id_jadwal]?.[ruang.ruang_id]?.[ruang.sesi_id] || {};
                    let jk;
                    if (pengawasJadwal.jam_ke) {
                        jk = pengawasJadwal.jam_ke > 0 ? pengawasJadwal.jam_ke : '1';
                    } else {
                        jk = jadwal.jam_ke || '1';
                    }
                    flatList.push({
                        ruang_id: ruang.ruang_id,
                        nama_ruang: ruang.nama_ruang,
                        sesi_id: ruang.sesi_id,
                        nama_sesi: ruang.nama_sesi,
                        kelas_id: ruang.kelas_id,
                        nama_kelas: ruang.nama_kelas,
                        jam_ke: jk,
                        jadwal: jadwal,
                    });
                });
            });
            flatList.sort((a, b) => {
                return a.jam_ke - b.jam_ke;
            });

            return flatList;
        }

        /**
         * Calculates rowspans for a given column.
         * @param {Array} items - The list of items to calculate counts for.
         * @param {...string} keys - The property keys to use for grouping (e.g., 'ruang_id').
         * @returns {Map} - A map of combined keys to their row counts.
         */
        function calculateRowCounts(items, ...keys) {
            const counts = new Map();
            let prevValues = null;
            let currentCount = 0;

            const getValues = (item) => keys.map(key => item?.[key]);

            [...items, null].forEach(item => {
                const currentValues = item ? getValues(item) : null;

                const isSameGroup = prevValues && currentValues &&
                    prevValues.every((val, i) => val === currentValues[i]);

                if (!isSameGroup) {
                    if (prevValues !== null) {
                        const key = prevValues.join('-');
                        counts.set(key, currentCount);
                    }
                    currentCount = 0;
                }

                if (item !== null) {
                    currentCount++;
                }
                prevValues = currentValues;
            });

            return counts;
        }

        /**
         * Builds the HTML string for the entire table.
         * @param {Array} flatSchedules - The flat list of schedule data.
         * @param {object} data - The full data object.
         * @param {Map} ruangRowCounts - Rowspan counts for the 'ruang' column.
         * @param {Map} sesiRowCounts - Rowspan counts for the 'sesi' column.
         * @returns {string} - The complete HTML table string.
         */
        function buildTable(flatSchedules, data, ruangRowCounts, sesiRowCounts) {
            const headerHtml = `
        <thead id="table-head">
            <tr class="bg-light">
                <th class="text-center align-middle">HARI/TANGGAL</th>
                <th class="text-center align-middle">RUANG</th>
                <th class="text-center align-middle">SESI</th>
                <th class="text-center align-middle">MAPEL<br>BANK SOAL</th>
                <th class="text-center align-middle">KELAS</th>
                <th class="text-center align-middle">JAM KE</th>
            </tr>
        </thead>`;

            let bodyHtml = '<tbody id="table-body">';
            const usedRuangKeys = new Set();
            const usedSesiKeys = new Set();
            let currentRow = 0;
            let ruang = '';
            let curSesi = 0;
            let bgRow = '';
            let bold = false;

            flatSchedules.forEach(item => {
                const { ruang_id, nama_ruang, sesi_id, nama_sesi, kelas_id, nama_kelas, jam_ke, jadwal } = item;
                const tgl = formatTanggal(jadwal.tgl_mulai);

                // Dapatkan nama kelas yang relevan dengan jadwal dan ruang
                const uniqueClassNames = [...new Set(jadwal.bank.bank_kelas.filter(id => kelas_id.includes(id)).map(id => nama_kelas[id]))];
                const bank_kelas = uniqueClassNames.join(', ');

                const idsJadwal = jadwal.id_jadwal;
                const bank_kode = `<span class="badge bg-light">${jadwal.bank.bank_kode}</span>`;

                bodyHtml += `<tr>`;

                // Kolom Tanggal (rowspan untuk seluruh tabel)
                if (currentRow === 0) {
                    bodyHtml += `<td rowspan="${flatSchedules.length}" class="text-center align-middle td-sesi">${tgl}</td>`;
                }
                bold = ruang !== nama_ruang

                // Kolom Ruang (rowspan)
                const ruangKey = String(ruang_id);
                if (!usedRuangKeys.has(ruangKey)) {
                    const rowspanValue = ruangRowCounts.get(ruangKey) || 1;
                    bodyHtml += `<td rowspan="${rowspanValue}"
                                class="text-center align-middle td-ruang border-top-bold border-left-bold
                                    border-bottom-bold">${nama_ruang}</td>`;
                    usedRuangKeys.add(ruangKey);
                    ruang = nama_ruang;
                }

                var genap = curSesi % 2 === 0;
                const bBold = bold ? 'border-top-bold' : '';
                // Kolom Sesi (rowspan)
                const sesiKey = `${ruang_id}-${sesi_id}`;
                if (!usedSesiKeys.has(sesiKey)) {
                    bgRow = genap ? 'bg-gray-light' : '';
                    const rowspanValue = sesiRowCounts.get(sesiKey) || 1;
                    bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-sesi ${bgRow} ${bBold}">${nama_sesi}</td>`;
                    usedSesiKeys.add(sesiKey);
                    curSesi++;
                }

                bodyHtml += `
            <td class="text-center align-middle td-mapel jadwal ${bgRow} ${bBold}" data-ruang="${ruang_id}" data-sesi="${sesi_id}" data-id="${idsJadwal}">
                ${jadwal.bank.mapel.nama_mapel}<br>${bank_kode}
            </td>
            <td class="text-center align-middle ${bgRow} ${bBold}">${bank_kelas}</td>
            <td class="td-alokasi align-middle border-rigt-bold ${bgRow} ${bBold}">
                    <input class="form-control form-control-sm" type="number" min="1" name="jamke" value="${jam_ke}">
            </td>
        </tr>`;

                currentRow++;
            });

            bodyHtml += '</tbody>';
            return `<table class="table table-bordered table-sm w-100" id="table-alokasi">${headerHtml}${bodyHtml}</table>`;
        }

        /**
         * Initializes UI elements like Select2 and buttons.
         */
        function initializeUI() {
            $('.select2').select2();
            $('button[type="submit"]').removeClass('d-none');
        }

        /**
         * Retrieves unique class names for a given room and its schedules.
         * @param {object} ruang_sesi - The room and session data.
         * @param {Array} jadwal - The exam schedules for the group.
         * @returns {Array} - An array of unique class names.
         */
        function getUniqueClassNames(ruang_sesi, jadwal) {
            const banks_kelas = [];
            const classIds = ruang_sesi.kelas_id;

            for (const j of jadwal) {
                for (const klsId of classIds) {
                    if (j.bank?.bank_kelas?.includes(klsId)) {
                        banks_kelas.push(ruang_sesi.nama_kelas[klsId] || '');
                    }
                }
            }

            return [...new Set(banks_kelas)];
        }

        /**
         * Helper function to format a date string.
         * @param {string} dateString - The date string to format.
         * @returns {string} - The formatted date.
         */
        function formatTanggal(dateString) {
            const date = new Date(dateString);
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            return date.toLocaleDateString('id-ID', options);
        }

    </script>