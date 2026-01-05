<?php
$map_sesi = [];

/**
 * Fungsi pembanding untuk usort().
 * Mengurutkan berdasarkan tgl_mulai (ascending), lalu jam_ke (ascending).
 * @param object $a Elemen pertama yang akan dibandingkan.
 * @param object $b Elemen kedua yang akan dibandingkan.
 * @return int Hasil perbandingan ( <0 jika $a harus lebih dulu, 0 jika sama, >0 jika $b harus lebih dulu).
 */
function sortByTglMulaiAndJamKe($a, $b)
{
    // 1. Urutkan berdasarkan tgl_mulai (dari jadwal[0])
    $tgl_a = $a->jadwal[0]->tgl_mulai;
    $tgl_b = $b->jadwal[0]->tgl_mulai;

    // Perbandingan string tanggal akan memberikan urutan kronologis yang benar
    if ($tgl_a !== $tgl_b) {
        return strcmp($tgl_a, $tgl_b);
    }

    // 2. Jika tgl_mulai sama, urutkan berdasarkan jam_ke
    // Pastikan konversi ke integer karena nilai aslinya adalah string
    $jam_a = (int) $a->jam_ke;
    $jam_b = (int) $b->jam_ke;

    if ($jam_a === $jam_b) {
        return 0; // Kedua elemen dianggap sama
    }

    return ($jam_a < $jam_b) ? -1 : 1; // Urutan ascending untuk jam_ke
}
?>
<main class="app-main pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title"><?= $subjudul ?></h3>
                    <div class="card-tools ms-auto">
                        <div class="btn-group shadow-sm">
                            <a href="#" class="btn btn-sm btn-primary active px-3">By Mapel</a>
                            <a href="<?= base_url('cbtalokasi/detail') ?>"
                                class="btn btn-sm btn-primary border px-3">Detail</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info shadow-sm align-content-center m-2" role="alert">
                        <div class="d-flex">
                            <i class="bi bi-info-circle me-3 mt-1 fs-5"></i>
                            <div>
                                <strong>Panduan Pengaturan:</strong>
                                <ol class="mb-0 mt-1">
                                    <li>Mengatur urutan aktif jadwal ujian sesuai urutan jam.</li>
                                    <li>Contoh: <b>Jam ke-2</b>, maka jadwal mapel akan aktif setelah jam pertama
                                        selesai dikerjakan oleh siswa.</li>
                                    <li>Jika semua <b>Jam ke</b> diatur ke 0 atau 1 maka semua jadwal yang aktif akan
                                        bisa dikerjakan secara bersamaan.</li>
                                    <li>Halaman ini berlaku jika semua jadwal telah diaktifkan di MENU JADWAL.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row mb-4">
                        <div class="col-12 col-sm-6 col-md-4">
                            <label for="jenis" class="form-label fw-bold">Jenis Penilaian</label>
                            <?php echo form_dropdown('jenis', $jenis, $jenis_selected, 'id="jenis" class="form-select form-select-sm"'); ?>
                        </div>
                    </div>
                    <?php
                    $map_jadwal = [];
                    foreach ($jadwals as $jadwal) {
                        $item_jadwal = (object) [
                            'ids_jadwal' => [],
                            'kelas' => [],
                            'sesi' => [],
                            'ruang' => [],
                            'jadwal' => []
                        ];
                        $keyJadwal = $jadwal->tgl_mulai . '-' . $jadwal->id_mapel;
                        if (!isset($map_jadwal[$keyJadwal])) {
                            $map_jadwal[$keyJadwal] = $item_jadwal;
                        }

                        $map_jadwal[$keyJadwal]->ids_jadwal[] = $jadwal->id_jadwal;
                        $map_jadwal[$keyJadwal]->jadwal[] = $jadwal;

                        $jadwal->ruang_sesi = [];

                        // ambil semua kelas_id dari bank_kelas jadwal
                        $kelasJadwal = array_filter(array_map(function ($k) {
                            return $k->kelas_id;
                        }, $jadwal->bank_kelas));

                        foreach ($sesis as $ruang) {
                            $keyRuang = $jadwal->id_jadwal . '-' . $ruang->ruang_id;
                            if (!in_array($keyRuang, $map_jadwal[$keyJadwal]->ruang)) {
                                $map_jadwal[$keyJadwal]->ruang[] = $keyRuang;
                                $map_jadwal[$keyJadwal]->id_ruang = $ruang->ruang_id;
                            }
                            $sesiMatch = clone $ruang;
                            $sesiMatch->sesi = [];
                            $sesiMatch->nama_kelas = [];
                            $sesiMatch->id_sesi = [];

                            foreach ($ruang->sesi as $sesi) {
                                $irisan = array_intersect($kelasJadwal, $sesi->kelas_id);
                                if (!empty($irisan)) {
                                    $sesiMatch->sesi[] = $sesi;
                                    $sesiMatch->id_sesi[] = $sesi->sesi_id;
                                    $keySesi = $jadwal->id_jadwal . '-' . $ruang->ruang_id . '-' . $sesi->sesi_id;
                                    if (!in_array($keySesi, $map_jadwal[$keyJadwal]->sesi)) {
                                        $map_jadwal[$keyJadwal]->sesi[] = $keySesi;
                                    }

                                    // hanya masukkan nama_kelas yang sesuai dengan kelas_id bank_kelas
                                    foreach ($irisan as $kelasId) {
                                        if (isset($sesi->nama_kelas[$kelasId])) {
                                            $sesiMatch->nama_kelas[] = $sesi->nama_kelas[$kelasId];
                                            $map_sesi[$sesi->sesi_id] = $sesi;
                                            if (!in_array($sesi->nama_kelas[$kelasId], $map_jadwal[$keyJadwal]->kelas)) {
                                                $map_jadwal[$keyJadwal]->kelas[] = $sesi->nama_kelas[$kelasId];
                                            }
                                        }
                                    }
                                }
                            }

                            if (!empty($sesiMatch->sesi)) {
                                // hilangkan duplikat nama kelas
                                $sesiMatch->nama_kelas = array_values(array_unique($sesiMatch->nama_kelas));

                                usort($sesiMatch->sesi, function ($a, $b) {
                                    return strnatcmp($a->kode_sesi, $b->kode_sesi);
                                });
                                usort($sesiMatch->nama_kelas, function ($a, $b) {
                                    return strnatcmp($a, $b);
                                });

                                $jadwal->ruang_sesi[] = $sesiMatch;
                            }
                        }

                        usort($jadwal->ruang_sesi, function ($a, $b) {
                            return strnatcmp($a->kode_ruang, $b->kode_ruang);
                        });
                    }

                    if (count($jadwals) > 0):
                        if ($jenis_selected != null): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mt-4 dataTable" id="tbl">
                                    <thead>
                                        <?php
                                        $rows = count($map_sesi) > 1 ? count($map_sesi) : 1;
                                        ?>
                                        <tr>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>">
                                                Hari & Tanggal
                                            </th>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>">
                                                Mapel
                                            </th>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>">
                                                Kelas
                                            </th>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>" style="width: 70px">
                                                Jam ke
                                            </th>
                                            <?php if (count($map_sesi) > 1): ?>
                                                <th class='text-center align-middle' colspan='<?= count($sesis) ?>'>Perkiraan Waktu
                                                    Sesi</th>
                                            <?php else:
                                                foreach ($map_sesi as $sesi): ?>
                                                    <th class='text-center align-middle' style="width: 120px">Waktu
                                                        <?= $sesi->kode_sesi ?>
                                                    </th>
                                                <?php endforeach; endif; ?>
                                        </tr>
                                        <?php if (count($map_sesi) > 1): ?>
                                            <tr>
                                                <?php foreach ($map_sesi as $sesi): ?>
                                                    <th class='text-center align-middle' style="width: 120px"><?= $sesi->kode_sesi ?>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endif; ?>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sortJadwal = [];
                                        foreach ($map_jadwal as $map_value) {
                                            $jamKe = $map_value->jadwal[0]->jam_ke ?? '1';
                                            foreach ($map_value->jadwal as $j) {
                                                foreach ($j->ruang_sesi as $rs) {
                                                    foreach ($rs->sesi as $s) {
                                                        $jam_jadwal = $pengawas[$j->id_jadwal][$rs->ruang_id][$s->sesi_id] ?? (object) [];
                                                        if ($jam_jadwal && isset($jam_jadwal->jam_ke) && $jam_jadwal->jam_ke > 0) {
                                                            $jamKe = $jam_jadwal->jam_ke;
                                                            break 3;
                                                        }
                                                    }
                                                }
                                            }
                                            $map_value->jam_ke = $jamKe;
                                            $sortJadwal[] = $map_value;
                                        }
                                        usort($sortJadwal, 'sortByTglMulaiAndJamKe');

                                        foreach ($sortJadwal as $value): ?>
                                            <tr data-jadwal="<?= $value->jadwal[0]->tgl_mulai ?>">
                                                <td class="align-middle text-center">
                                                    <?= buat_tanggal(date('D, d M Y', strtotime($value->jadwal[0]->tgl_mulai ?? ''))) ?>
                                                </td>
                                                <td class="align-middle text-center"><?= $value->jadwal[0]->nama_mapel ?></td>
                                                <td class="align-middle text-center"><?= implode(', ', $value->kelas) ?></td>
                                                <td class="text-center align-middle jam-ke" data-jamke="<?= $value->jam_ke ?>"
                                                    data-id="<?= implode(',', $value->ids_jadwal) ?>"
                                                    data-sesi="<?= implode(',', $value->sesi) ?>" data-tp="<?= $tp_active->id_tp ?>"
                                                    data-smt="<?= $smt_active->id_smt ?>">
                                                    <input class="form-control form-control-sm input-jam text-center shadow-sm"
                                                        type="number" min="1" name="jamke" value="<?= $value->jam_ke ?>"
                                                        style="width: 60px; margin: 0 auto;">
                                                </td>
                                                <?php foreach ($map_sesi as $sesi): ?>
                                                    <td class="text-center align-middle text-bold text-xs sesi waktu-<?= $sesi->sesi_id ?>"
                                                        data-sesi="<?= $sesi->sesi_id ?>"
                                                        data-mapel="<?= $value->jadwal[0]->id_mapel ?>" style="color: #a7b2bd;"></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <?= form_open('', array('id' => 'simpanalokasi')) ?>
                        <button type="submit" class="btn btn-primary shadow-sm mt-3 float-end ms-auto px-4">
                            <i class="bi bi-save me-2 text-white"></i> Simpan Alokasi
                        </button>
                        <?= form_close() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    </div>
    <script src="<?= base_url() ?>/assets/app/js/jquery.rowspanizer.js"></script>
    <script>
        let allSesi = [];

        $(document).ready(function () {
            ajaxcsrf();
            $("#tbl").rowspanizer({ columns: [0, 1] });

            allSesi = <?= json_encode(array_values($map_sesi)) ?>;
            var opsiJenis = $("#jenis");

            opsiJenis.change(function () {
                getAllJadwal();
            });

            function getAllJadwal() {
                var jenis = opsiJenis.val();
                var url = base_url + 'cbtalokasi?jenis=' + jenis;
                if (jenis) {
                    console.log(url);
                    window.location.href = url;
                }
            }

            $('.input-jam').on('input', function () {
                hitungAlokasi()
            });

            $('#simpanalokasi').on('submit', function (e) {
                e.stopPropagation();
                e.preventDefault();
                e.stopImmediatePropagation();

                // Improved iteration with debugging
                const $inputs = $('#tbl input[name="jamke"]');
                var jsonObj = [];

                $inputs.each((i, el) => {
                    const input = $(el);
                    const jam_ke = input.val();
                    const td = input.closest('td');

                    if (td.length === 0) {
                        return;
                    }

                    // Force string retrieval
                    const id_jadwal = td.attr('data-id');
                    if (!id_jadwal) {
                        return;
                    }

                    const jadwals = String(id_jadwal).split(',');

                    const raw_sesi = td.attr('data-sesi');
                    const sesis = raw_sesi ? String(raw_sesi).split(',') : [];

                    const id_tp = td.attr('data-tp');
                    const id_smt = td.attr('data-smt');

                    let matched = false;
                    for (const jad of jadwals) {
                        const adaSesi = sesis.filter(s => s.indexOf(jad + '-') === 0);

                        if (adaSesi.length > 0) {
                            matched = true;
                            for (const sesi of adaSesi) {
                                const parts = sesi.split('-');
                                const id_pengawas = id_tp + id_smt + jad + parts[1] + parts[2];

                                let item = {};
                                item["id_pengawas"] = id_pengawas;
                                item["id_jadwal"] = jad;
                                item["id_sesi"] = parts[2];
                                item["id_tp"] = id_tp;
                                item["id_smt"] = id_smt;
                                item["id_ruang"] = parts[1];
                                item["jam_ke"] = jam_ke;
                                jsonObj.push(item);
                            }
                        } else {
                            // Fallback: Send minimal data to update cbt_jadwal jam_ke
                            let item = {};
                            item["id_pengawas"] = "0"; // Signal to skip cbt_pengawas logic
                            item["id_jadwal"] = jad;
                            item["jam_ke"] = jam_ke;
                            // Dummy values for safety
                            item["id_sesi"] = "0";
                            item["id_tp"] = id_tp;
                            item["id_smt"] = id_smt;
                            item["id_ruang"] = "0";
                            jsonObj.push(item);
                        }
                    }
                });

                if (jsonObj.length === 0) {
                    Swal.fire({
                        title: "Info",
                        text: "Tidak ada data jadwal yang valid untuk disimpan.",
                        icon: "info"
                    });
                    return;
                }

                var dataPost = $(this).serialize() + "&alokasi=" + JSON.stringify(jsonObj);
                console.log(dataPost);

                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

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
                                html: "Alokasi waktu ujian berhasil disimpan",
                                icon: "success",
                                showCancelButton: false,
                                confirmButtonColor: "#3085d6",
                                confirmButtonText: "OK"
                            }).then(result => {
                                if (result.isConfirmed || result.value) {
                                    window.location.reload();
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
                        let errMsg = "Terjadi kesalahan server";
                        try {
                            const err = JSON.parse(xhr.responseText);
                            errMsg = err.Message || errMsg;
                        } catch (e) {
                            errMsg += " (" + status + ")";
                        }

                        Swal.fire({
                            title: "Error",
                            text: errMsg,
                            icon: "error"
                        });
                    }
                });
            });

            $('#selector button').click(function () {
                $(this).addClass('active').siblings().addClass('btn-outline-primary').removeClass('active btn-primary');

                if (!$('#by-kelas').is(':hidden')) {
                    $('#by-kelas').addClass('d-none');
                    $('#by-ruang').removeClass('d-none');
                } else {
                    $('#by-kelas').removeClass('d-none');
                    $('#by-ruang').addClass('d-none');
                }
            });

            hitungAlokasi()
        })

        function hitungAlokasi() {
            if (allSesi.length > 0) {
                let data_mapel = [];
                const $rows = $('#tbl').find('tr'), headers1 = $rows.splice(0, 1);
                $rows.each((i, row) => {
                    const td = $(row).find('.jam-ke');
                    const jam_ke = td.find('input[name="jamke"]').val() + '';
                    const id_sesi = td.data('sesi') + '';
                    const data_sesi = id_sesi.split(',');
                    if (jam_ke) {
                        for (const sesi of data_sesi) {
                            const parts = sesi.split('-')[2];
                            const td_sesi = $(row).find('.sesi');
                            const td_mapel = td_sesi.data('mapel') + ''
                            if (parts && !data_mapel.some(m => m.id_mapel === td_mapel && m.jam_ke === jam_ke)) {
                                data_mapel.push({
                                    id_mapel: td_mapel,
                                    jam_ke: jam_ke === '0' ? '1' : jam_ke,
                                });
                            }
                        }
                    }
                })

                const timeToMinutes = (time) => {
                    const [h, m] = time.split(':').map(Number);
                    return h * 60 + m;
                };

                const minutesToTime = (totalMinutes) => {
                    const hours = Math.floor(totalMinutes / 60);
                    const minutes = totalMinutes % 60;
                    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                };

                const maxJamKe = Math.max(...data_mapel.map(m => Number(m.jam_ke)));

                const mapelByJamKe = {};
                data_mapel.forEach(mapel => {
                    const jamKe = mapel.jam_ke;
                    if (!mapelByJamKe[jamKe]) {
                        mapelByJamKe[jamKe] = [];
                    }
                    mapelByJamKe[jamKe].push(mapel);
                });

                allSesi.forEach(sesi => {
                    sesi.mapel = [];
                    const totalDuration = timeToMinutes(sesi.waktu_akhir) - timeToMinutes(sesi.waktu_mulai);

                    const durationPerSlot = totalDuration / maxJamKe;

                    for (let i = 1; i <= maxJamKe; i++) {
                        const jamKe = String(i);

                        const startMinutes = timeToMinutes(sesi.waktu_mulai) + (i - 1) * durationPerSlot;
                        const endMinutes = startMinutes + durationPerSlot;

                        const start_time = minutesToTime(startMinutes);
                        const end_time = minutesToTime(endMinutes);

                        if (mapelByJamKe[jamKe]) {
                            mapelByJamKe[jamKe].forEach(mapel => {
                                sesi.mapel.push({
                                    ...mapel,
                                    dari: start_time,
                                    sampai: end_time,
                                    durasi: durationPerSlot
                                });

                                const tds_sesi = $('.sesi');
                                tds_sesi.each((i, td) => {
                                    const td_mapel = $(td).data('mapel') + '';
                                    const td_sesi = $(td).data('sesi') + '';
                                    if (td_mapel === mapel.id_mapel && td_sesi === sesi.sesi_id) {
                                        $(td).text((start_time || '--') + ' ~ ' + (end_time || '--'));
                                    }
                                });
                            });
                        }
                    }
                });
                console.log('allSesi', allSesi);
            }
        }
    </script>