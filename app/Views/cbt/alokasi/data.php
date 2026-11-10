<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$map_sesi = [];

/**
 * Fungsi pembanding untuk usort().
 * Mengurutkan berdasarkan tgl_mulai (ascending), lalu jam_ke (ascending).
 */
if (!function_exists('sortByTglMulaiAndJamKe')) {
    function sortByTglMulaiAndJamKe($a, $b)
    {
        $tgl_a = $a->jadwal[0]->tgl_mulai ?? '';
        $tgl_b = $b->jadwal[0]->tgl_mulai ?? '';

        if ($tgl_a !== $tgl_b) {
            return strcmp($tgl_a, $tgl_b);
        }

        $jam_a = (int) ($a->jam_ke ?? 0);
        $jam_b = (int) ($b->jam_ke ?? 0);

        if ($jam_a === $jam_b) {
            return 0;
        }

        return ($jam_a < $jam_b) ? -1 : 1;
    }
}
?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-primary card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title"><?= $subjudul ?? 'Alokasi Waktu Ujian' ?></h3>
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
                            <select name="jenis" id="jenis" class="form-select form-select-sm">
                                <option value="">-- Pilih Jenis --</option>
                                <?php if (isset($jenis) && is_array($jenis)): ?>
                                    <?php foreach ($jenis as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= ($jenis_selected ?? '') == $key ? 'selected' : '' ?>>
                                            <?= $value ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <?php
                    $map_jadwal = [];
                    $jadwals = $jadwals ?? [];
                    $sesis = $sesis ?? [];
                    $pengawas = $pengawas ?? [];

                    foreach ($jadwals as $jadwal) {
                        $item_jadwal = (object) [
                            'ids_jadwal' => [],
                            'kelas' => [],
                            'sesi' => [],
                            'ruang' => [],
                            'jadwal' => []
                        ];
                        $tgl_mulai = $jadwal->tgl_mulai ?? '';
                        $id_mapel = $jadwal->id_mapel ?? '';
                        $keyJadwal = $tgl_mulai . '-' . $id_mapel;
                        if (!isset($map_jadwal[$keyJadwal])) {
                            $map_jadwal[$keyJadwal] = $item_jadwal;
                        }

                        $map_jadwal[$keyJadwal]->ids_jadwal[] = $jadwal->id_jadwal;
                        $map_jadwal[$keyJadwal]->jadwal[] = $jadwal;

                        $jadwal->ruang_sesi = [];

                        $kelasJadwal = [];
                        if (isset($jadwal->bank_kelas) && is_array($jadwal->bank_kelas)) {
                            $kelasJadwal = array_filter(array_map(function ($k) {
                                return $k->kelas_id ?? null;
                            }, $jadwal->bank_kelas));
                        }

                        foreach ($sesis as $ruang) {
                            $keyRuang = ($jadwal->id_jadwal ?? '') . '-' . ($ruang->ruang_id ?? '');
                            if (!in_array($keyRuang, $map_jadwal[$keyJadwal]->ruang)) {
                                $map_jadwal[$keyJadwal]->ruang[] = $keyRuang;
                                $map_jadwal[$keyJadwal]->id_ruang = $ruang->ruang_id;
                            }
                            $sesiMatch = clone $ruang;
                            $sesiMatch->sesi = [];
                            $sesiMatch->nama_kelas = [];
                            $sesiMatch->id_sesi = [];

                            if (isset($ruang->sesi) && is_array($ruang->sesi)) {
                                foreach ($ruang->sesi as $sesi) {
                                    $sesiKelasId = is_array($sesi->kelas_id ?? null) ? $sesi->kelas_id : [];
                                    $irisan = array_intersect($kelasJadwal, $sesiKelasId);
                                    if (!empty($irisan)) {
                                        $sesiMatch->sesi[] = $sesi;
                                        $sesiMatch->id_sesi[] = $sesi->sesi_id;
                                        $keySesi = ($jadwal->id_jadwal ?? '') . '-' . ($ruang->ruang_id ?? '') . '-' . ($sesi->sesi_id ?? '');
                                        if (!in_array($keySesi, $map_jadwal[$keyJadwal]->sesi)) {
                                            $map_jadwal[$keyJadwal]->sesi[] = $keySesi;
                                        }

                                        foreach ($irisan as $kelasId) {
                                            $namaKelasArr = (array) ($sesi->nama_kelas ?? []);
                                            if (isset($namaKelasArr[$kelasId])) {
                                                $namaKelas = $namaKelasArr[$kelasId];
                                                $sesiMatch->nama_kelas[] = $namaKelas;
                                                $map_sesi[$sesi->sesi_id] = $sesi;
                                                if (!in_array($namaKelas, $map_jadwal[$keyJadwal]->kelas)) {
                                                    $map_jadwal[$keyJadwal]->kelas[] = $namaKelas;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if (!empty($sesiMatch->sesi)) {
                                $sesiMatch->nama_kelas = array_values(array_unique($sesiMatch->nama_kelas));
                                usort($sesiMatch->sesi, function ($a, $b) {
                                    return strnatcmp($a->kode_sesi ?? '', $b->kode_sesi ?? '');
                                });
                                usort($sesiMatch->nama_kelas, function ($a, $b) {
                                    return strnatcmp($a ?? '', $b ?? '');
                                });
                                $jadwal->ruang_sesi[] = $sesiMatch;
                            }
                        }

                        usort($jadwal->ruang_sesi, function ($a, $b) {
                            return strnatcmp($a->kode_ruang ?? '', $b->kode_ruang ?? '');
                        });
                    }

                    if (count($jadwals) > 0):
                        if ($jenis_selected != null): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mt-4 dataTable" id="tbl">
                                    <thead>
                                        <?php $rows = count($map_sesi) > 1 ? count($map_sesi) : 1; ?>
                                        <tr>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>">Hari & Tanggal</th>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>">Mapel</th>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>">Kelas</th>
                                            <th class="text-center align-middle" rowspan="<?= $rows ?>" style="width: 70px">Jam
                                                ke</th>
                                            <?php if (count($map_sesi) > 1): ?>
                                                <th class='text-center align-middle' colspan='<?= count($sesis) ?>'>Perkiraan Waktu
                                                    Sesi</th>
                                            <?php else:
                                                foreach ($map_sesi as $sesi): ?>
                                                    <th class='text-center align-middle' style="width: 120px">Waktu
                                                        <?= $sesi->kode_sesi ?? '' ?></th>
                                                <?php endforeach; endif; ?>
                                        </tr>
                                        <?php if (count($map_sesi) > 1): ?>
                                            <tr>
                                                <?php foreach ($map_sesi as $sesi): ?>
                                                    <th class='text-center align-middle' style="width: 120px text-sm">
                                                        <?= $sesi->kode_sesi ?? '' ?></th>
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
                                                foreach (($j->ruang_sesi ?? []) as $rs) {
                                                    foreach (($rs->sesi ?? []) as $s) {
                                                        $jam_jadwal = $pengawas[$j->id_jadwal][$rs->ruang_id][$s->sesi_id] ?? null;
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
                                            <tr data-jadwal="<?= $value->jadwal[0]->tgl_mulai ?? '' ?>">
                                                <td class="align-middle text-center">
                                                    <?= date('D, d M Y', strtotime($value->jadwal[0]->tgl_mulai ?? 'now')) ?>
                                                </td>
                                                <td class="align-middle text-center"><?= $value->jadwal[0]->nama_mapel ?? '' ?></td>
                                                <td class="align-middle text-center"><?= implode(', ', $value->kelas) ?></td>
                                                <td class="text-center align-middle jam-ke" data-jamke="<?= $value->jam_ke ?>"
                                                    data-id="<?= implode(',', $value->ids_jadwal) ?>"
                                                    data-sesi="<?= implode(',', $value->sesi) ?>"
                                                    data-tp="<?= $tp_active->id_tp ?? '' ?>"
                                                    data-smt="<?= $smt_active->id_smt ?? '' ?>">
                                                    <input class="form-control form-control-sm input-jam text-center shadow-sm"
                                                        type="number" min="1" name="jamke" value="<?= $value->jam_ke ?>"
                                                        style="width: 60px; margin: 0 auto;">
                                                </td>
                                                <?php foreach ($map_sesi as $sesi): ?>
                                                    <td class="text-center align-middle text-bold text-xs sesi waktu-<?= $sesi->sesi_id ?? '' ?>"
                                                        data-sesi="<?= $sesi->sesi_id ?? '' ?>"
                                                        data-mapel="<?= $value->jadwal[0]->id_mapel ?? '' ?>" style="color: #a7b2bd;">
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <form id="simpanalokasi">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary mt-3 float-end ms-auto px-4 shadow-sm">
                                <i class="bi bi-save me-2"></i> Simpan Alokasi
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/jquery.rowspanizer.js"></script>
<script>
    let allSesi = [];

    $(document).ready(function () {
        ajaxcsrf();
        if ($.fn.rowspanizer) {
            $("#tbl").rowspanizer({ columns: [0, 1] });
        }

        allSesi = <?= json_encode(array_values($map_sesi)) ?>;
        var opsiJenis = $("#jenis");

        opsiJenis.change(function () {
            getAllJadwal();
        });

        function getAllJadwal() {
            var jenis = opsiJenis.val();
            var url = base_url + 'cbtalokasi?jenis=' + jenis;
            if (jenis) {
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

            const $inputs = $('#tbl input[name="jamke"]');
            var jsonObj = [];

            $inputs.each((i, el) => {
                const input = $(el);
                const jam_ke = input.val();
                const td = input.closest('td');

                if (td.length === 0) return;

                const id_jadwal = td.attr('data-id');
                if (!id_jadwal) return;

                const jadwals = String(id_jadwal).split(',');
                const raw_sesi = td.attr('data-sesi');
                const sesis = raw_sesi ? String(raw_sesi).split(',') : [];

                const id_tp = td.attr('data-tp');
                const id_smt = td.attr('data-smt');

                for (const jad of jadwals) {
                    const adaSesi = sesis.filter(s => s.indexOf(jad + '-') === 0);

                    if (adaSesi.length > 0) {
                        for (const sesi of adaSesi) {
                            const parts = sesi.split('-');
                            const id_pengawas = id_tp + jad + parts[1] + parts[2];

                            jsonObj.push({
                                id_pengawas: id_pengawas,
                                id_jadwal: jad,
                                id_sesi: parts[2],
                                id_tp: id_tp,
                                id_smt: id_smt,
                                id_ruang: parts[1],
                                jam_ke: jam_ke
                            });
                        }
                    } else {
                        jsonObj.push({
                            id_pengawas: "0",
                            id_jadwal: jad,
                            jam_ke: jam_ke,
                            id_sesi: "0",
                            id_tp: id_tp,
                            id_smt: id_smt,
                            id_ruang: "0"
                        });
                    }
                }
            });

            if (jsonObj.length === 0) {
                Swal.fire({ title: "Info", text: "Tidak ada data jadwal yang valid untuk disimpan.", icon: "info" });
                return;
            }

            var dataPost = $(this).serialize() + "&alokasi=" + JSON.stringify(jsonObj);

            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: base_url + "cbtalokasi/saveAlokasi",
                type: "POST",
                dataType: "JSON",
                data: dataPost,
                success: function (data) {
                    if (data.status) {
                        Swal.fire({
                            title: "Sukses",
                            html: "Alokasi waktu ujian berhasil disimpan",
                            icon: "success"
                        }).then(result => {
                            if (result.isConfirmed || result.value) {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({ title: "Info", text: data.message || "Tidak ada data yang disimpan", icon: "info" });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({ title: "Error", text: "Terjadi kesalahan server", icon: "error" });
                }
            });
        });

        hitungAlokasi()
    })

    function hitungAlokasi() {
        if (allSesi.length > 0) {
            let data_mapel = [];
            const $rows = $('#tbl tbody tr');
            $rows.each((i, row) => {
                const td = $(row).find('.jam-ke');
                const jam_ke = td.find('input[name="jamke"]').val() + '';
                const id_sesi_raw = td.attr('data-sesi') + '';
                const data_sesi = id_sesi_raw.split(',');
                if (jam_ke) {
                    for (const sesi of data_sesi) {
                        const parts = sesi.split('-');
                        const td_sesi = $(row).find('.sesi');
                        const td_mapel = td_sesi.data('mapel') + ''
                        if (parts[2] && !data_mapel.some(m => m.id_mapel === td_mapel && m.jam_ke === jam_ke)) {
                            data_mapel.push({
                                id_mapel: td_mapel,
                                jam_ke: jam_ke === '0' ? '1' : jam_ke,
                            });
                        }
                    }
                }
            })

            const timeToMinutes = (time) => {
                if (!time || typeof time !== 'string') return 0;
                const parts = time.split(':');
                if (parts.length < 2) return 0;
                const [h, m] = parts.map(Number);
                return (isNaN(h) ? 0 : h) * 60 + (isNaN(m) ? 0 : m);
            };

            const minutesToTime = (totalMinutes) => {
                if (isNaN(totalMinutes) || totalMinutes < 0) totalMinutes = 0;
                const hours = Math.floor(totalMinutes / 60);
                const minutes = Math.floor(totalMinutes % 60);
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
            };

            const durations = data_mapel.map(m => Number(m.jam_ke));
            const maxJamKe = durations.length > 0 ? Math.max(...durations) : 1;

            const mapelByJamKe = {};
            data_mapel.forEach(mapel => {
                const jamKe = mapel.jam_ke;
                if (!mapelByJamKe[jamKe]) mapelByJamKe[jamKe] = [];
                mapelByJamKe[jamKe].push(mapel);
            });

            allSesi.forEach(sesi => {
                // Skip sesi with missing time data
                if (!sesi.waktu_mulai || !sesi.waktu_akhir) {
                    console.warn('Sesi missing waktu_mulai or waktu_akhir:', sesi);
                    return;
                }
                
                sesi.mapel = [];
                const startMinutesTotal = timeToMinutes(sesi.waktu_mulai);
                const endMinutesTotal = timeToMinutes(sesi.waktu_akhir);
                const totalDuration = endMinutesTotal - startMinutesTotal;
                
                if (totalDuration <= 0 || maxJamKe <= 0) return;
                
                const durationPerSlot = totalDuration / maxJamKe;

                for (let i = 1; i <= maxJamKe; i++) {
                    const jamKe = String(i);
                    const startMinutes = startMinutesTotal + (i - 1) * durationPerSlot;
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

                            $('.sesi').each((idx, td) => {
                                const td_mapel = $(td).data('mapel') + '';
                                const td_sesi = $(td).data('sesi') + '';
                                if (td_mapel === mapel.id_mapel && td_sesi === String(sesi.sesi_id)) {
                                    $(td).text((start_time || '--') + ' ~ ' + (end_time || '--'));
                                }
                            });
                        });
                    }
                }
            });
        }
    }
</script>
<?= $this->endSection() ?>