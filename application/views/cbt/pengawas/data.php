<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title"><b><?= $subjudul ?></b></h3>
                </div>
                <div class="card-body">
                    <?php
                    $totalJadwal = isset($jadwal) && is_array($jadwal) ? count($jadwal) : 0;
                    $totalRuang = isset($ruang) && is_array($ruang) ? count($ruang) : 0;
                    $totalGuru = isset($gurus) && is_array($gurus) ? count(array_filter(array_keys($gurus))) : 0;
                    ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan Pengawas Ujian CBT</h5>
                                <div class="text-muted">Pengaturan pengawas untuk TP <b><?= $tp_active->tahun ?></b> Semester <b><?= $smt_active->smt ?></b></div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Jadwal: <?= $totalJadwal ?></span>
                                <span class="badge badge-info px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Ruang: <?= $totalRuang ?></span>
                                <span class="badge badge-success px-3 py-2 text-sm">Guru Terdaftar: <?= $totalGuru ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Penunjukan Pengawas</h5>
                        <ul class="pl-3 mb-0">
                            <li>Gunakan tombol <span class="badge badge-primary">Generate</span> untuk menjadikan guru mapel sebagai pengawas otomatis.</li>
                            <li>Pengawas dapat diedit manual per ruang dan sesi sebelum disimpan.</li>
                            <li>Setelah menyimpan, pastikan jadwal sudah diaktifkan agar pengawas muncul di rekap ujian.</li>
                        </ul>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="jenis">Jenis</label>
                                <?php
                                echo form_dropdown('jenis', $jenis, null, 'id="jenis" class="form-control form-control-sm"'); ?>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="jenis">Tanggal</label>
                                <select name="tanggal" id="tanggal" class="form-control form-control-sm"></select>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <strong class="mb-2">Otomatis</strong>
                            <button id="btn-auto" class="btn btn-primary btn-sm" disabled>Generate</button>
                        </div>
                    </div>
                    <div class="table-responsive" id="table-container"></div>
                    <?= form_open('', array('id' => 'savepengawas')) ?>
                    <button type="submit" class="btn btn-primary card-tools mb-3 mt-3 float-right d-none">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="<?= base_url() ?>/assets/app/js/jquery.rowspanizer.js"></script>
<script>
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
            var url = base_url + 'cbtpengawas/get_tanggal?jenis=' + jenis;
            if (!kosong) {
                $.ajax({
                    url: url,
                    type: "GET",
                    success: function (respon) {
                        console.log(respon)
                        for (const tgl in respon) {
                            const mulai = new Date(tgl).toLocaleDateString('id-ID', {
                                year: "numeric",
                                month: "short",
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
            var kosong = jenis == "" || opsiTgl.val() == "";

            let formData = new FormData($('#savepengawas')[0]);
            formData.append('jenis', jenis)
            formData.append('tanggal', opsiTgl.val())

            if (!kosong) {
                swal.fire({
                    title: "Memuat data pengawas",
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });

                $.ajax({
                    url: base_url + 'cbtpengawas/get_pengawas',
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

        $('#savepengawas').on('submit', function (e) {
            e.stopPropagation();
            e.preventDefault();
            e.stopImmediatePropagation();

            var jenis = opsiJenis.val();
            var kosong = jenis == ""; //ruang == '' || sesi == "" ||
            if (kosong) return;

            swal.fire({
                title: "Menyimpan pengawas ujian",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });

            const tables = $('#table-pengawas');
            var jsonObj = [];
            const $rows1 = $(tables).find('tr'), headers1 = $rows1.splice(0, 1);
            $rows1.each((i, row) => {
                const ruang = $(row).find('.jadwal').data('ruang');//opsiRuang.val();
                const sesi = $(row).find('.jadwal').data('sesi');//opsiSesi.val();
                const idJadwals = ($(row).find('.jadwal').data('id')).toString();
                const jadwals = idJadwals?.split(',');
                const guru = $(row).find('.pengawas').val();

                for (const jadwal of jadwals) {
                    let item = {};
                    item ["jadwal"] = jadwal;
                    item ["ruang"] = ruang;
                    item ["sesi"] = sesi;
                    item ["guru"] = guru;

                    jsonObj.push(item);
                }
            });

            console.log('table', jsonObj);
            var dataPost = $(this).serialize() + "&data=" + JSON.stringify(jsonObj);

            setTimeout(() => {
                $.ajax({
                    url: base_url + "cbtpengawas/savepengawas",
                    type: "POST",
                    dataType: "JSON",
                    data: dataPost,
                    success: function (data) {
                        console.log("response:", data);
                        if (data.status) {
                            swal.fire({
                                title: "Sukses",
                                html: "Pengawas ujian berhasil disimpan",
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
                            swal.fire({
                                title: "Info",
                                text: "Tidak ada data yang disimpan",
                                icon: "info"
                            });
                        }
                    }, error: function (xhr, status, error) {
                        console.log("response:", xhr.responseText);
                        console.log("response:", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            }, 1000);
        });

        $('#btn-auto').on('click', function () {
            swal.fire({
                title: "Mengatur pengawas ujian",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });

            const tdp = $('.td-pengawas');
            tdp.each((ind, td) => {
                const jadwal_guru = ($(td).data('guru')).toString();
                const splited = jadwal_guru?.split(',');
                console.log('split', splited)
                const select = $(td).find('select.pengawas');
                const oldVal = select.val();
                console.log('sel', oldVal)
                for (const guru of splited) {
                    if (guru && !oldVal.includes(guru)) {
                        oldVal.push(guru);
                    }
                }
                select.val(oldVal).trigger('change');
            });

            setTimeout(() => {$('#savepengawas').submit();}, 500);
        });

        opsiJenis.val('').trigger('change');
    })

    /**
     * Main function to create and populate the exam schedule table from JSON data.
     * @param {object} data - The JSON data containing exam schedules, rooms, and supervisors.
     */
    async function createTableFromJSON(data) {
        console.log('data', data)
        const tableContainer = document.getElementById('table-container');
        const allSesi = data.ruangs || [];
        const jsonData = data.jadwals_ujian || [];

        if (!validateData(allSesi, jsonData, tableContainer)) {
            return;
        }

        // 1. Proses data menjadi daftar datar (flat list)
        const flatSchedules = processDataToFlatList(jsonData, allSesi, data);
        console.log('flatSchedules', flatSchedules)

        // 2. Urutkan daftar untuk penggabungan rowspan yang benar
        flatSchedules.sort((a, b) => {
            if (a.ruang_id !== b.ruang_id) return a.ruang_id - b.ruang_id;
            if (a.sesi_id !== b.sesi_id) return a.sesi_id - b.sesi_id;
            return a.jam_ke - b.jam_ke;
        });

        // 3. Hitung rowspan untuk 'ruang' dan 'sesi'
        const ruangRowCounts = calculateRowCounts(flatSchedules, 'ruang_id');
        const sesiRowCounts = calculateRowCounts(flatSchedules, 'ruang_id', 'sesi_id');

        // 4. Bangun dan render tabel HTML
        const tableHtml = buildTable(flatSchedules, data, ruangRowCounts, sesiRowCounts);
        tableContainer.innerHTML = tableHtml;

        // 5. Inisialisasi Select2 dan tombol
        initializeUI(flatSchedules);
    }

    /**
     * Validates if the necessary data arrays are not empty.
     */
    function validateData(allSesi, jsonData, container) {
        if (allSesi.length === 0 || jsonData.length === 0) {
            container.innerHTML = `<table class="w-100 table-bordered"><tr><td class="alert alert-default-warning text-center">Tidak ada jadwal penilaian hari ini</td></tr></table>`;
            return false;
        }
        return true;
    }

    /**
     * Processes and creates a flat list of schedules for each room.
     * @param {Array} jsonData - The exam schedules.
     * @param {Array} allSesi - The available rooms and sessions.
     * @param {object} data - The full data object.
     * @returns {Array} - A flat list where each item represents one schedule in one room.
     */
    function processDataToFlatList(jsonData, allSesi, data) {
        const flatList = [];

        jsonData.forEach(jadwal => {
            jadwal.bank.bank_kelas = jadwal.bank.bank_kelas.map(String)
            const ruangFiltered = allSesi.filter(r =>
                r.kelas_id.some(id => jadwal.bank.bank_kelas.includes(id?.toString()))
            );

            console.log('ruangFiltered', ruangFiltered)

            ruangFiltered.forEach(ruang => {
                const pengawasJadwal = data.pengawas[jadwal.id_jadwal]?.[ruang.ruang_id]?.[ruang.sesi_id] || {};
                const pengawasList = (pengawasJadwal.id_guru || '').split(',').filter(Boolean);
                const jamJadwal =  () => {
                    if (pengawasJadwal?.jam_ke && pengawasJadwal?.jam_ke > 0) {
                        return pengawasJadwal?.jam_ke;
                    } else if (jadwal.jam_ke && jadwal.jam_ke > 0) {
                        return jadwal.jam_ke;
                    }
                    return '1';
                };


                flatList.push({
                    ruang_id: ruang.ruang_id,
                    nama_ruang: ruang.nama_ruang,
                    sesi_id: ruang.sesi_id,
                    nama_sesi: ruang.nama_sesi,
                    kelas_id: ruang.kelas_id,
                    nama_kelas: ruang.nama_kelas,
                    jam_ke: jamJadwal(),
                    jadwal: jadwal,
                    pengawas: pengawasList
                });
            });
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
                <th class="text-center align-middle alert-default-info">HARI/TANGGAL</th>
                <th class="text-center align-middle alert-default-warning">RUANG</th>
                <th class="text-center align-middle alert-default-primary">SESI</th>
                <th class="text-center align-middle alert-default-info">JAM KE</th>
                <th class="text-center align-middle alert-default-success">MAPEL<br>BANK SOAL</th>
                <th class="text-center align-middle alert-default-primary">KELAS</th>
                <th class="text-center align-middle alert-default-danger" style="width: 400px; max-width: 400px">PENGAWAS</th>
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
            const { ruang_id, nama_ruang, sesi_id, nama_sesi, kelas_id, nama_kelas, jam_ke, jadwal, pengawas } = item;
            const tgl = formatTanggal(jadwal.tgl_mulai);

            // Dapatkan nama kelas yang relevan dengan jadwal dan ruang
            const uniqueClassNames = [...new Set(jadwal.bank.bank_kelas.filter(id => kelas_id.includes(id)).map(id => nama_kelas[id]))];
            const bank_kelas = uniqueClassNames.join(', ');

            const idsJadwal = jadwal.id_jadwal;
            const idsGuru = jadwal.bank.bank_guru_id;
            const optionGuru = createPengawasSelect(data.gurus, pengawas);
            const bank_kode = `<span class="badge badge-primary">${jadwal.bank.bank_kode}</span>`;

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
                bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-ruang
                                border-top-bold border-left-bold border-bottom-bold">${nama_ruang}</td>`;
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
            <td class="text-center align-middle td-jam ${bgRow} ${bBold}">${jam_ke}</td>
            <td class="text-center align-middle td-mapel jadwal ${bgRow} ${bBold}" data-ruang="${ruang_id}" data-sesi="${sesi_id}" data-id="${idsJadwal}">
                ${jadwal.bank.mapel.nama_mapel}<br>${bank_kode}
            </td>
            <td class="text-center align-middle ${bgRow} ${bBold}">${bank_kelas}</td>
            <td class="td-pengawas border-rigt-bold ${bgRow} ${bBold}" data-guru="${idsGuru}">${optionGuru}</td>
        </tr>`;

            currentRow++;
        });

        bodyHtml += '</tbody>';
        return `<table class="table table-bordered table-sm w-100" id="table-pengawas">${headerHtml}${bodyHtml}</table>`;
    }

    function initializeUI(schedules) {
        if (schedules.length > 0) {
            $('#btn-auto').removeAttr('disabled');
        }
        $('.select2').select2({ width: '100%' });
        $('button[type="submit"]').removeClass('d-none');
    }

    function createPengawasSelect(gurus, selectedPengawas) {
        let options = '';
        for (const id_guru in gurus) {
            if (id_guru > 0) {
                const isSelected = selectedPengawas.includes(id_guru) ? 'selected' : '';
                options += `<option value="${id_guru}" ${isSelected}>${gurus[id_guru]}</option>`;
            }
        }
        return `<select name="pengawas[]" style="width: 100%" class="select2 form-control form-control-sm pengawas" multiple="multiple" data-placeholder="Pilih Pengawas" required>${options}</select>`;
    }

    function formatTanggal(dateString) {
        const date = new Date(dateString);
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

</script>
