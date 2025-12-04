<?php
$map_sesi = [];
?>
<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title"><b><?= $subjudul ?></b></h3>
                    <div class="btn-group card-tools">
                        <a href="#" type="button" class="btn btn-sm btn-outline-primary active">By Mapel</a>
                        <a href="<?= base_url('cbtalokasi/detail') ?>" type="button"
                           class="btn btn-sm btn-outline-primary">Detail</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $totalJadwal = is_array($jadwals) ? count($jadwals) : 0;
                    $mapelSet = [];
                    $tanggalSet = [];
                    if (is_array($jadwals)) {
                        foreach ($jadwals as $jadwal) {
                            if (!empty($jadwal->nama_mapel)) {
                                $mapelSet[$jadwal->nama_mapel] = true;
                            }
                            if (!empty($jadwal->tgl_mulai)) {
                                $tanggalSet[$jadwal->tgl_mulai] = true;
                            }
                        }
                    }
                    $totalMapel = count($mapelSet);
                    $totalTanggal = count($tanggalSet);
                    ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan Alokasi Waktu CBT</h5>
                                <div class="text-muted">Kelola urutan jam berlangsungnya jadwal ujian</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Jadwal: <?= $totalJadwal ?></span>
                                <span class="badge badge-info px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Mapel Unik: <?= $totalMapel ?></span>
                                <span class="badge badge-success px-3 py-2 text-sm">Tanggal Terjadwal: <?= $totalTanggal ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengaturan Jam</h5>
                        <ul class="pl-3 mb-0">
                            <li>Gunakan kolom <b>Jam ke</b> untuk menentukan urutan aktif tiap jadwal dalam satu hari.</li>
                            <li>Jika semua jam diisi 0/1, semua jadwal aktif bersamaan untuk siswa.</li>
                            <li>Pastikan jadwal sudah diaktifkan agar alokasi waktu ini diterapkan.</li>
                            <li>Tanpa penyimpanan di halaman ini, urutan mengikuti pengaturan di menu <b>Jadwal</b>.</li>
                        </ul>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="jenis">Jenis</label>
                                <?php echo form_dropdown('jenis', $jenis, $jenis_selected, 'id="jenis" class="form-control"'); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $map_jadwal = [];
                    foreach ($jadwals as $jadwal) {
                        $item_jadwal = (object)[
                            'ids_jadwal' => [],
                            'kelas' => [],
                            'sesi' => [],
                            'ruang' => [],
                            'jadwal' => []
                        ];
                        $keyJadwal = $jadwal->tgl_mulai.'-'.$jadwal->id_mapel;
                        if (!isset($map_jadwal[$keyJadwal])) {
                            $map_jadwal[$keyJadwal] = $item_jadwal;
                        }

                        $map_jadwal[$keyJadwal]->ids_jadwal[] = $jadwal->id_jadwal;
                        $map_jadwal[$keyJadwal]->jadwal[] = $jadwal;

                        $jadwal->ruang_sesi = [];

                        // ambil semua kelas_id dari bank_kelas jadwal
                        $kelasJadwal = array_filter(array_map(function($k){
                            return $k->kelas_id;
                        }, $jadwal->bank_kelas));

                        foreach ($sesis as $ruang) {
                            $keyRuang = $jadwal->id_jadwal.'-'.$ruang->ruang_id;
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
                                    $keySesi = $jadwal->id_jadwal.'-'.$ruang->ruang_id.'-'.$sesi->sesi_id;
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

                                usort($sesiMatch->sesi, function($a, $b) {
                                    return strnatcmp($a->kode_sesi, $b->kode_sesi);
                                });
                                usort($sesiMatch->nama_kelas, function($a, $b) {
                                    return strnatcmp($a, $b);
                                });

                                $jadwal->ruang_sesi[] = $sesiMatch;
                            }
                        }

                        usort($jadwal->ruang_sesi, function($a, $b) {
                            return strnatcmp($a->kode_ruang, $b->kode_ruang);
                        });
                    }

                    //echo '<pre>';
                    //var_dump($map_jadwal);
                    //echo '</pre>';
                    if (count($jadwals) > 0) :
                        if ($jenis_selected != null) :?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mt-4" id="tbl">
                                    <thead>
                                    <?php
                                    $rows = count($map_sesi)>1 ? count($map_sesi) : 1;
                                    ?>
                                    <tr class="bg-light">
                                        <th class="text-center align-middle" rowspan="<?=$rows?>">
                                            Hari & Tanggal
                                        </th>
                                        <th class="text-center align-middle" rowspan="<?=$rows?>">
                                            Mapel
                                        </th>
                                        <th class="text-center align-middle" rowspan="<?=$rows?>">
                                            Kelas
                                        </th>
                                        <th class="text-center align-middle" rowspan="<?=$rows?>" style="width: 70px">
                                            Jam ke
                                        </th>
                                        <?php if (count($map_sesi)>1) :?>
                                            <th class='text-center align-middle' colspan='<?=count($sesis)?>'>Perkiraan Waktu Sesi</th>
                                        <?php else:
                                            foreach ($map_sesi as $sesi) :?>
                                                <th class='text-center align-middle' style="width: 120px">Waktu <?=$sesi->kode_sesi?></th>
                                            <?php endforeach; endif;?>
                                    </tr>
                                    <?php if (count($map_sesi)>1) : ?>
                                        <tr class="bg-light">
                                            <?php foreach ($map_sesi as $sesi): ?>
                                                <th class='text-center align-middle' style="width: 120px"><?=$sesi->kode_sesi?></th>
                                            <?php endforeach;?>
                                        </tr>
                                    <?php endif; ?>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($map_jadwal as $value):
                                        $jamKe = $value->jadwal[0]->jam_ke ?? '1';
                                        foreach ($value->jadwal as $j) {
                                            foreach ($j->ruang_sesi as $rs) {
                                                foreach ($rs->sesi as $s) {
                                                    $jam_jadwal = $pengawas[$j->id_jadwal][$rs->ruang_id][$s->sesi_id] ?? (object)[];
                                                    if ($jam_jadwal && isset($jam_jadwal->jam_ke) && $jam_jadwal->jam_ke > 0) {
                                                        $jamKe = $jam_jadwal->jam_ke;
                                                        break 3;
                                                    }
                                                }
                                            }
                                        }

                                        ?>
                                        <tr data-jadwal="<?=$value->jadwal[0]->tgl_mulai?>">
                                            <td class="align-middle text-center">
                                                <?= buat_tanggal(date('D, d M Y', strtotime($value->jadwal[0]->tgl_mulai ?? ''))) ?>
                                            </td>
                                            <td class="align-middle text-center"><?= $value->jadwal[0]->nama_mapel ?></td>
                                            <td class="align-middle text-center"><?= implode(', ', $value->kelas) ?></td>
                                            <td class="text-center align-middle jam-ke"
                                                data-jamke="<?= $jamKe ?>"
                                                data-id="<?= implode(',', $value->ids_jadwal) ?>"
                                                data-sesi="<?= implode(',', $value->sesi) ?>"
                                                data-tp="<?= $tp_active->id_tp ?>"
                                                data-smt="<?= $smt_active->id_smt ?>">
                                                <input class="form-control form-control-sm input-jam" type="number" min="1" name="jamke"
                                                       value="<?= $jamKe ?>">
                                            </td>
                                            <?php foreach ($map_sesi as $sesi) :?>
                                                <td class="text-center align-middle text-bold text-xs sesi waktu-<?=$sesi->sesi_id?>"
                                                    data-sesi="<?= $sesi->sesi_id ?>"
                                                    data-mapel="<?= $value->jadwal[0]->id_mapel ?>"
                                                    style="color: #a7b2bd;"></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <?= form_open('', array('id' => 'simpanalokasi')) ?>
                        <button type="submit" class="btn btn-primary card-tools mt-3 float-right">
                            <i class="fas fa-save mr-2"></i>Simpan
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
        $("#tbl").rowspanizer({columns: [0,1]});

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

            const $rows1 = $('#tbl').find('tr'), headers1 = $rows1.splice(0, 1);
            var jsonObj = [];
            $rows1.each((i, row) => {
                const td = $(row).find('.jam-ke');
                const jam_ke = td.find('input[name="jamke"]').val();
                const id_jadwal = td.data('id')+'';
                const jadwals = id_jadwal.split(',');

                const id_sesi = td.data('sesi')+'';
                const sesis = id_sesi.split(',');

                const id_tp = td.data('tp')+'';
                const id_smt = td.data('smt')+'';

                for (const jad of jadwals) {
                    const adaSesi = sesis.filter(s => s.startsWith(jad+'-'));
                    if (adaSesi.length > 0) {
                        for (const sesi of adaSesi) {
                            const parts = sesi.split('-');
                            const id_pengawas = id_tp+id_smt+jad+parts[1]+parts[2];

                            let item = {};
                            item ["id_pengawas"] = id_pengawas;
                            item ["id_jadwal"] = jad;
                            item ["id_sesi"] = parts[2];
                            item ["id_tp"] = id_tp;
                            item ["id_smt"] = id_smt;
                            item ["id_ruang"] = parts[1];
                            item ["jam_ke"] = jam_ke;
                            jsonObj.push(item);
                        }
                    }
                }
            });
            console.log('json', jsonObj);

            if (jsonObj.length === 0) return;

            var dataPost = $(this).serialize() + "&alokasi=" + JSON.stringify(jsonObj);
            console.log(dataPost);

            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
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
                        swal.fire({
                            title: "Sukses",
                            html: "Alokasi waktu ujian berhasil disimpan",
                            icon: "success",
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "OK"
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
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
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
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
                const jam_ke = td.find('input[name="jamke"]').val()+'';
                const id_sesi = td.data('sesi')+'';
                const data_sesi = id_sesi.split(',');
                if (jam_ke) {
                    for (const sesi of data_sesi) {
                        const parts = sesi.split('-')[2];
                        const td_sesi = $(row).find('.sesi');
                        const td_mapel = td_sesi.data('mapel')+''
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
                                const td_mapel = $(td).data('mapel')+'';
                                const td_sesi = $(td).data('sesi')+'';
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
